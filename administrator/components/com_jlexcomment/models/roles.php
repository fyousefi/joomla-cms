<?php
/**
 * @package     JLex Comment
 * @version     1.0.0
 * @copyright   Copyright (C) 2013-2016 JLexArt Team (http://www.jlexart.com). All rights reserved.
 * @license     GNU/GPL or later
 * @author      JLexArt (support@jlexart.com)
 */

defined ( '_JEXEC' ) or die ();

class JLexCommentModelRoles extends JModelLegacy
{
    protected $limit = 20;

    protected $limitstart = 0;

    protected $total = 0;

    public function getRoles()
    {
        $app = JFactory::getApplication();
        $whereClauses = array();

        $query = $this->getDbo()->getQuery(true);
        $query->select ("SQL_CALC_FOUND_ROWS roles.*")
              ->from ("#__jlexcomment_roles roles")
              ->select ("u.name AS author")
              ->leftJoin ("#__users AS u ON roles.created_by=u.id")

              ->select ("gp.title AS group_name")
              ->leftJoin ("#__usergroups gp ON roles.group_id=gp.id");

        // where clauses
        $keywords   = $app->getUserStateFromRequest ( "jcm.roles.query", "filter_search", "");
    
        $this->setState('jcm.roles.query', $keywords);

        if (!preg_match("/^\s*$/", $keywords))
        {
            $keywords = $this->getDbo()->quote("%" . $keywords . "%");
            $whereClauses[] = "roles.title LIKE $keywords";
        }

        if (count($whereClauses) > 0)
        {
            $query->where ($whereClauses);
        }

        // sort by
        $sortBy = $app->getUserStateFromRequest ( "jcm.roles.sortby", "filter_order", "roles.created_time");
        $sortDir = $app->getUserStateFromRequest ( "jcm.roles.sortdir", "filter_order_Dir", "desc");

        $sortBy_allows = array ("roles.title", "roles.group_id", "roles.created_by",
                                "roles.created_time", "roles.published");
        $sortDir_allows = array ("desc", "asc");

        if (!array_key_exists($sortBy, $sortBy_allows))
        {
            $sortBy = "roles.created_time";
        }

        if (!array_key_exists($sortDir, $sortDir_allows))
        {
            $sortDir = "desc";
        }
        
        $this->setState('jcm.roles.sortby', $sortBy);
        $this->setState('jcm.roles.sortdir', $sortDir);

        $query->order( $sortBy . " " . $sortDir);

        $this->limitstart = $app->getUserStateFromRequest ( "jcm.roles.limitstart", 'limitstart', 0, "int" );
        $this->limit = $app->getUserStateFromRequest ("jcm.roles.limit", 'limit', $this->limit, 20, "int" );

        $roles = $this->getDbo()->setQuery($query, $this->limitstart, $this->limit)->loadObjectList();

        $this->total = $this->getDbo()->setQuery( "SELECT FOUND_ROWS()" )->loadResult ();

        if (! $roles)
        {
            return null;
        }

        foreach ($roles as $k=>&$role)
        {
            $role->url2author = JUri::base (true) . '/index.php?option=com_users&task=user.edit&id=' . $role->created_by;
            $role->url2edit = JUri::base (true) . "/index.php?option=com_jlexcomment&view=roles&layout=form&id=" . $role->id;
        }

        return $roles;
    }


    public function getPageNav() {
        jimport('joomla.html.pagination');
        return new JPagination($this->total, $this->limitstart, $this->limit );
    }

    // for object single
    public $id = 0;

    public function getRole ()
    {
        if($this->id<1) return null;

        $query = $this->_db->getQuery(true);
        $query->select("*")
              ->from("#__jlexcomment_roles")
              ->where("id=" . $this->id);

        $item = $this->_db->setQuery($query)->loadObject();

        return $item;
    }

    public function getForm ()
    {
        $path = dirname(__FILE__)."/forms/role.xml";
        $form = JForm::getInstance("jcm_role", $path);

        if($this->id>0)
        {
            $item = $this->getRole();
            if(!$item)
            {
                throw new Exception(JText::_("JCM_PAGE_NOT_FOUND"), 404);
                return false;
            }

            $form->bind($item);
        }

        return $form;
    }

    public function save()
    {
        $app = JFactory::getApplication();
        $data = $app->input->getArray([
                "group_id" => "int",
                "title" => "string",
                "colour" => "string",
                "created_by" => "int",
                "created_time" => "string",
                "published" => "int",
                "id" => "int"
            ]);

        $row = $this->getTable("Roles", "TableCm");

        $row->bind($data);

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

    public $jcm_state = 0;

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
        $query->update("#__jlexcomment_roles")
              ->set("published={$state}")
              ->where("id IN(".implode(",", $cid).")");

        // make query
        $this->_db->setQuery($query)->execute();

        return true;
    }

    public function remove()
    {   
        $app = JFactory::getApplication();
        $cid = array_key_exists('cid', $_POST) ? $_POST['cid'] : null;

        if(!is_array($cid) || !count($cid)) 
        {
            throw new Exception(JText::_("JCM_NO_ROW_FOUND_TO_TASK"), 500);
        }

        $state = $this->state>0?1:0;

        $query = $this->_db->getQuery(true);
        $query->delete("#__jlexcomment_roles")
              ->where("id IN(".implode(",", $cid).")");

        // make query
        $this->_db->setQuery($query)->execute();

        return true;
    }
}
