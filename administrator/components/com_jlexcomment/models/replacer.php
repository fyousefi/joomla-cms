<?php
/**
 * @package     JLex Comment
 * @version     1.0.0
 * @copyright   Copyright (C) 2013-2016 JLexArt Team (http://www.jlexart.com). All rights reserved.
 * @license     GNU/GPL or later
 * @author      JLexArt (support@jlexart.com)
 */

defined ( '_JEXEC' ) or die ();

class JLexCommentModelReplacer extends JModelLegacy
{
    protected $limit = 20;

    protected $limitstart = 0;

    protected $total = 0;

    public function getReplaceClauses ()
    {
        $app = JFactory::getApplication();
        $whereClauses = array();

        $query = $this->getDbo()->getQuery(true);
        $query->select ("SQL_CALC_FOUND_ROWS r.*")
              ->from ("#__jlexcomment_replacer r")
              ->select ("u.name AS author")
              ->leftJoin ("#__users AS u ON r.created_by=u.id");

        // where clauses
        $keywords   = $app->getUserStateFromRequest ( "jcm.rp.query", "filter_search", "");

        $this->setState('jcm.rp.query', $keywords);

        if (!preg_match("/^\s*$/", $keywords))
        {
            $keywords = $this->getDbo()->quote("%" . $keywords . "%");
            $whereClauses[] = "r.caption LIKE $keywords";
        }

        if (count($whereClauses) > 0)
        {
            $query->where ($whereClauses);
        }

        // sort by
        $sortBy = $app->getUserStateFromRequest ( "jcm.rp.sortby", "filter_order", "r.created_time");
        $sortDir = $app->getUserStateFromRequest ( "jcm.rp.sortdir", "filter_order_Dir", "desc");

        // safe
        $sortDir = $sortDir=="desc" ? "desc" : "asc";
        $sortByColumns = array (
                "r.created_time",
                "r.caption",
                "r.created_by",
                "r.published"
            );

        if (!in_array($sortBy, $sortByColumns))
        {
            $sortBy = $sortByColumns[0];
        }
        
        $this->setState('jcm.rp.sortby', $sortBy);
        $this->setState('jcm.rp.sortdir', $sortDir);

        $query->order( $sortBy . " " . $sortDir);

        $this->limitstart = $app->getUserStateFromRequest ( "jcm.rp.limitstart", 'limitstart', 0, "int" );
        $this->limit = $app->getUserStateFromRequest ("jcm.rp.limit", 'limit', $this->limit, 20, "int" );

        $clauses = $this->getDbo()->setQuery($query, $this->limitstart, $this->limit)->loadObjectList();

        $this->total = $this->getDbo()->setQuery( "SELECT FOUND_ROWS()" )->loadResult ();

        if (! $clauses)
        {
            return null;
        }

        // get user groups
        $query->clear ()
              ->select ("id,title")
              ->from ("#__usergroups");
        $usergroups = $this->_db->setQuery ($query)->loadObjectList ('id');

        foreach ($clauses as $k=>&$clause)
        {
            $clause->url2edit   = JUri::base (true) . '/index.php?option=com_jlexcomment&view=replacer&layout=form&id=' . $clause->id;  
            $clause->group_cid  = explode(",", $clause->group_cid);

            $group2string = array ();
            foreach ($clause->group_cid as $group)
            {
                $group2string[] = $usergroups [$group]->title;
            }

            $clause->usergroups = implode(", ", $group2string);
        }

        return $clauses;
    }

    public function getPageNav() {
        jimport ( 'joomla.html.pagination' );
        return new JPagination ( $this->total, $this->limitstart, $this->limit );
    }

    // for object single
    public $id = 0;

    public function getReplaceClause()
    {
        if($this->id<1) return null;

        $query = $this->_db->getQuery(true);
        
        $query->select("*")
              ->from("#__jlexcomment_replacer")
              ->where("id=" . $this->id);

        $group = $this->_db->setQuery($query)->loadObject();

        if(!$group) return null;

        $group->group_cid = explode(",", $group->group_cid);

        return $group;
    }

    public function getForm()
    {
        $path = dirname(__FILE__)."/forms/replacer.xml";
        $form = JForm::getInstance("jcm_replacer", $path);

        if($this->id>0)
        {
            $group = $this->getReplaceClause();
            if(!$group)
            {
                throw new Exception(JText::_("JCM_PAGE_NOT_FOUND"), 404);
            }

            $form->bind(array('jform'=> $group));
        }

        return $form;
    }

    public function save()
    {
        $app = JFactory::getApplication ();
        $request = $app->input->get('jform', array(), 'array');

        $data = $app->input->getArray([
                "jform" => [
                    "id" => "int",
                    "caption" => "string",
                    "regexClause" => "raw",
                    "replaceClause" => "raw",
                    "created_by" => "int",
                    "group_cid" => "array",
                    "published" => "int"
                ]
            ]);

        $row = $this->getTable("Replacer", "TableCm");
        $row->bind($data["jform"]);

        if(!$row->check())
        {
            $this->setError($row->getError());
            return false;
        }

        if(!$row->store())
        {
            $this->setError (JText::_("JCM_APPEAR_ERROR_WHEN_SAVING_YOUR_DATE_TRY_LATER"));
            return false;
        }

        return $row->id;
    }

    public function remove()
    {
        $app = JFactory::getApplication();
        $cid = array_key_exists('cid', $_POST) ? $_POST['cid'] : null;

        if(!is_array($cid) || !count($cid)) 
        {
            throw new Exception(jtext::_("JCM_NO_ROW_FOUND_TO_TASK"), 500);
        }

        $query = $this->_db->getQuery(true);
        $query->delete("#__jlexcomment_replacer")
              ->where("id IN(".implode(",", $cid).")");

        // make query
        $this->_db->setQuery($query)->execute();

        return true;
    }

    public $jcm_state = 1;

    public function state ()
    {
        $app = JFactory::getApplication();
        $cid = array_key_exists('cid', $_POST) ? $_POST['cid'] : null;

        if(!is_array($cid) || !count($cid)) 
        {
            throw new Exception(jtext::_("JCM_NO_ROW_FOUND_TO_TASK"), 500);
        }

        $state = $this->jcm_state>0?1:0;

        $query = $this->_db->getQuery(true);
        $query->update("#__jlexcomment_replacer")
              ->set("published={$state}")
              ->where("id IN(".implode(",", $cid).")");

        // make query
        $this->_db->setQuery($query)->execute();

        return true;
    }
}
