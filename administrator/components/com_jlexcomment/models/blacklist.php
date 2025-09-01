<?php
/**
 * @package     JLex Comment
 * @version     1.0.0
 * @copyright   Copyright (C) 2013-2016 JLexArt Team (http://www.jlexart.com). All rights reserved.
 * @license     GNU/GPL or later
 * @author      JLexArt (support@jlexart.com)
 */

defined ( '_JEXEC' ) or die ();

class JLexCommentModelBlacklist extends JModelLegacy
{
    protected $limit = 20;

    protected $limitstart = 0;

    protected $total = 0;

    public function getBlacklist ()
    {
        $app = JFactory::getApplication();
        $whereClauses = array();

        $query = $this->getDbo()->getQuery(true);
        $query->select ("SQL_CALC_FOUND_ROWS bls.*")
              ->from ("#__jlexcomment_blacklist bls")

              ->select ("u.name AS author")
              ->leftJoin ("#__users AS u ON bls.created_by=u.id")

              ->select ("u2.name AS user_blocked")
              ->leftJoin ("#__users u2 ON (bls.method_value=u2.id AND bls.method=1)");

        // where clauses
        $keywords   = $app->getUserStateFromRequest ( "jcm.bls.query", "filter_search", "");
        $method   = $app->getUserStateFromRequest ( "jcm.bls.method", "filter_method", "0");
    
        $this->setState('jcm.bls.query', $keywords);
        $this->setState('jcm.bls.method', $method);

        if (!preg_match("/^\s*$/", $keywords))
        {
            $keywords = $this->getDbo()->quote("%" . $keywords . "%");
            $whereClauses[] = "(bls.method_value LIKE $keywords || bls.reason LIKE $keywords || u2.name LIKE $keywords)";
        }

        switch ($method)
        {
            case 1:
            case 2:
            case 3:
                $whereClauses[] = "bls.method=" . $method;
                break;
        }

        if (count($whereClauses) > 0)
        {
            $query->where ($whereClauses);
        }

        // sort by
        $sortBy = $app->getUserStateFromRequest ( "jcm.bls.sortby", "filter_order", "bls.created_time");
        $sortDir = $app->getUserStateFromRequest ( "jcm.bls.sortdir", "filter_order_Dir", "desc");

        // safe
        $sortDir = $sortDir=="desc" ? "desc" : "asc";
        $sortByColumns = array (
                "bls.created_time",
                "bls.created_by"
            );

        if (!in_array($sortBy, $sortByColumns))
        {
            $sortBy = $sortByColumns[0];
        }

        $this->setState('jcm.bls.sortby', $sortBy);
        $this->setState('jcm.bls.sortdir', $sortDir);

        $query->order( $sortBy . " " . $sortDir);

        $this->limitstart = $app->getUserStateFromRequest ( "jcm.bls.limitstart", 'limitstart', 0, "int" );
        $this->limit = $app->getUserStateFromRequest ("jcm.bls.limit", 'limit', $this->limit, 20, "int" );

        $blacklist = $this->getDbo()->setQuery($query, $this->limitstart, $this->limit)->loadObjectList();

        $this->total = $this->getDbo()->setQuery( "SELECT FOUND_ROWS()" )->loadResult ();

        if (! $blacklist)
        {
            return null;
        }

        foreach ($blacklist as $k=>&$item)
        {
            if ($item->method==1)
            {
                $item->method_text = "User (" . $item->method_value.")";
                $item->method_value = $item->user_blocked;
            } elseif ($item->method==2) {
                $item->method_text = "Email";
            } else {
                $item->method_text = "IP address";
            }

            $item->url2edit = JUri::base (true) . "/index.php?option=com_jlexcomment&view=blacklist&layout=form&id=" . $item->id;
            $item->url2author = JUri::base (true) . '/index.php?option=com_users&task=user.edit&id=' . $item->created_by;
        }

        return $blacklist;
    }


    public function getPageNav() {
        jimport ( 'joomla.html.pagination' );
        return new JPagination ( $this->total, $this->limitstart, $this->limit );
    }

    // for object single
    public $id = 0;

    public function getBlockItem ()
    {
        if($this->id<1) return null;

        $query = $this->_db->getQuery(true);
        $query->select("*")
              ->from("#__jlexcomment_blacklist")
              ->where("id=" . $this->id);

        $item = $this->_db->setQuery($query)->loadObject ();

        if($item)
        {
            // J4 form not allow use method name
            $item->mt = $item->method;

            if($item->method==1)
            {
                $item->method_user = $item->method_value;
            } else {
                $item->method_other = $item->method_value;
            }
        }

        return $item;
    }

    public function getForm()
    {
        $path = dirname(__FILE__)."/forms/blacklist.xml";
        $form = JForm::getInstance("jcm_sub", $path);

        if($this->id>0)
        {
            $item = $this->getBlockItem();
            if(!$item)
            {
                throw new Exception(JText::_("JCM_PAGE_NOT_FOUND"), 404);
            }

            $form->bind($item);
        }

        return $form;
    }

    public function save()
    {
        $app = JFactory::getApplication();
        $data = $app->input->getArray([
                "mt" => "int",
                "method_other" => "string",
                "method_user" => "int",
                "reason" => "string",
                "created_by" => "int",
                "created_time" => "string",
                "id" => "int"
            ]);

        $row = $this->getTable("Blacklist", "TableCm");

        $row->bind($data);

        $row->set("method", $data["mt"]);
        $row->set("method_value", $data[($data["mt"]==1?"method_user":"method_other")]);

        if(!$row->check())
        {
            $this->setError($row->getError());
            return false;
        }

        if(!$row->store())
        {
            $this->setError(JText::_("JCM_APPEAR_ERROR_WHEN_SAVING_YOUR_DATE_TRY_LATER"));
            return false;
        }

        return $row->id;
    }

    public function remove()
    {   
        $app = JFactory::getApplication();
        $cid = array_key_exists('cid', $_POST) ? $_POST['cid'] : null;

        if(!is_array($cid) || ! count($cid)) 
        {
            throw new Exception(JText::_("JCM_NO_ROW_FOUND_TO_TASK"), 500);
        }

        $state = $this->state>0?1:0;

        $query = $this->_db->getQuery(true);
        $query->delete("#__jlexcomment_blacklist")
              ->where("id IN(".implode(",", $cid).")");

        // make query
        $this->_db->setQuery($query)->execute();

        return true;
    }
}
