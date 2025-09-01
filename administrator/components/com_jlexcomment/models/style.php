<?php
/**
 * @package     JLex Comment
 * @version     1.0.0
 * @copyright   Copyright (C) 2013-2016 JLexArt Team (http://www.jlexart.com). All rights reserved.
 * @license     GNU/GPL or later
 * @author      JLexArt (support@jlexart.com)
 */

defined ( '_JEXEC' ) or die ();

class JLexCommentModelStyle extends JModelLegacy
{
    protected $limit = 20;

    protected $limitstart = 0;

    protected $total = 0;

    public function getStyles()
    {
        $app = JFactory::getApplication();
        $wClauses = array();

        $query = $this->_db->getQuery(true);
        $query->select("SQL_CALC_FOUND_ROWS s.*")
              ->from("#__jlexcomment_style s");

        // where clauses
        $keywords   = $app->getUserStateFromRequest("jcm.st.query", "filter_search", "");
        $publish_state = $app->getUserStateFromRequest("jcm.st.publish", "filter_state", "-1");

        $this->setState('jcm.st.query', $keywords);
        $this->setState('jcm.st.publish', $publish_state);

        if(!preg_match("/^\s*$/", $keywords))
        {
            $keywords = $this->_db->quote("%" . $keywords . "%");
            $wClauses[] = "s.caption LIKE $keywords";
        }

        if($publish_state!=-1)
        {
            $wClauses[]="s.published=".$publish_state;
        }

        if(count($wClauses)>0)
        {
            $query->where($wClauses);
        }

        // sort by
        $sortBy = $app->getUserStateFromRequest("jcm.st.sortby", "filter_order", "s.created_time");
        $sortDir = $app->getUserStateFromRequest("jcm.st.sortdir", "filter_order_Dir", "desc");

        // safe
        $sortDir = $sortDir=="desc" ? "desc" : "asc";
        $sortByColumns = array(
                "s.id",
                "s.caption",
                "s.published"
            );

        if(!in_array($sortBy, $sortByColumns))
        {
            $sortBy = $sortByColumns[0];
        }

        $this->setState('jcm.st.sortby', $sortBy);
        $this->setState('jcm.st.sortdir', $sortDir);

        $query->order($sortBy . " " . $sortDir);

        $this->limitstart = $app->getUserStateFromRequest("jcm.st.limitstart", 'limitstart', 0, "int" );
        $this->limit = $app->getUserStateFromRequest("jcm.st.limit", 'limit', $this->limit, 20, "int" );

        $styles = $this->_db->setQuery($query, $this->limitstart, $this->limit)->loadObjectList();

        $this->total = $this->_db->setQuery("SELECT FOUND_ROWS()" )->loadResult();

        if(!$styles) return null;

        foreach($styles as $k=>&$style)
        {
            $style->url2edit = JUri::base (true).'/index.php?option=com_jlexcomment&view=style&layout=form&id='.$style->id;
        }

        return $styles;
    }

    public function getPageNav()
    {
        jimport('joomla.html.pagination');
        return new JPagination($this->total, $this->limitstart, $this->limit);
    }

    public function getStyle()
    {
        if($this->id<0) return null;

        $query = $this->_db->getQuery(true);
        $query->select('*')
              ->from('#__jlexcomment_style')
              ->where('id=' . $this->id);

        $row = $this->_db->setQuery($query)->loadObject();

        return $row;
    }

    public function getForm ()
    {
        $path = dirname (__FILE__) . "/forms/style.xml";
        $form = JForm::getInstance("jcm_style", $path);

        if($this->id>0)
        {
            $style = $this->getStyle();
            if(!$style)
            {
                throw new Exception(JText::_("JCM_PAGE_NOT_FOUND"), 404);
            }

            $form->bind(array('jform'=>$style));
        }

        return $form;
    }

    public function save()
    {
        $app  = JFactory::getApplication ();
        $data = $app->input->getArray([
                "jform" => [
                    "id" => "int",
                    "caption" => "string",
                    "css" => "string",
                    "maxlength" => "int",
                    "published" => "int"
                ]
            ]);

        $row = $this->getTable("Style", "TableCm");
        $row->bind($data["jform"]);

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

    public function remove ()
    {
        $cid = array_key_exists('cid', $_POST)?$_POST['cid'] : null;

        if(!is_array($cid) || !count($cid)) 
        {
            throw new Exception(JText::_("JCM_NO_ROW_FOUND_TO_TASK"), 500);
        }

        $result = array(
                'total' => count($cid),
                'success' => 0
            );

        $query = $this->_db->getQuery(true);
        $query->delete("#__jlexcomment_style")
              ->where("id IN(".implode(",", $cid).")");

        $this->_db->setQuery($query)->execute();

        return $result;
    }

    public $jcm_state = 1;

    public function state()
    {
        $app = JFactory::getApplication();
        $cid = array_key_exists('cid', $_POST) ? $_POST['cid'] : null;

        if(!is_array($cid) || !count($cid)) 
        {
            throw new Exception(JText::_("JCM_NO_ROW_FOUND_TO_TASK"), 500);
        }

        $state = $this->jcm_state>0?1:0;

        $query = $this->_db->getQuery(true);
        $query->update("#__jlexcomment_style")
              ->set("published={$state}")
              ->where("id IN(".implode(",", $cid).")");

        // make query
        $this->_db->setQuery($query)->execute();

        return true;
    }
}
