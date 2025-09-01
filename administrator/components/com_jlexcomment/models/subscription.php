<?php
/**
 * @package     JLex Comment
 * @version     1.0.0
 * @copyright   Copyright (C) 2013-2016 JLexArt Team (http://www.jlexart.com). All rights reserved.
 * @license     GNU/GPL or later
 * @author      JLexArt (support@jlexart.com)
 */

defined ( '_JEXEC' ) or die();

class JLexCommentModelSubscription extends JModelLegacy
{
    protected $limit = 20;

    protected $limitstart = 0;

    protected $total = 0;

    public function getUsers()
    {
        $app = JFactory::getApplication();
        $whereClauses = array();

        $query = $this->getDbo()->getQuery(true);
        $query->select ("SQL_CALC_FOUND_ROWS sub.*")
              ->from ("#__jlexcomment_subscribe sub")
              ->select ("u.name AS author")
              ->leftJoin ("#__users AS u ON sub.created_by=u.id")

              ->select ("obj.title AS object_name")
              ->leftJoin ("#__jlexcomment_obj obj ON sub.obj_id=obj.id");

        // where clauses
        $keywords   = $app->getUserStateFromRequest ( "jcm.sub.query", "filter_search", "");
    
        $this->setState('jcm.sub.query', $keywords);

        if (!preg_match("/^\s*$/", $keywords))
        {
            $keywords = $this->getDbo()->quote("%" . $keywords . "%");
            $whereClauses[] = "(sub.name LIKE $keywords || sub.email LIKE $keywords)";
        }

        if (count($whereClauses) > 0)
        {
            $query->where ($whereClauses);
        }

        // sort by
        $sortBy = $app->getUserStateFromRequest ( "jcm.sub.sortby", "filter_order", "sub.created_time");
        $sortDir = $app->getUserStateFromRequest ( "jcm.sub.sortdir", "filter_order_Dir", "desc");

        // safe
        $sortDir = $sortDir=="desc" ? "desc" : "asc";
        $sortByColumns = array (
                "sub.created_time",
                "sub.name",
                "sub.email",
                "sub.obj_id",
                "sub.created_by",
                "sub.email_confirmed"
            );

        if (!in_array($sortBy, $sortByColumns))
        {
            $sortBy = $sortByColumns[0];
        }

        $this->setState('jcm.sub.sortby', $sortBy);
        $this->setState('jcm.sub.sortdir', $sortDir);

        $query->order( $sortBy . " " . $sortDir);

        $this->limitstart = $app->getUserStateFromRequest ( "jcm.sub.limitstart", 'limitstart', 0, "int" );
        $this->limit = $app->getUserStateFromRequest ("jcm.sub.limit", 'limit', $this->limit, 20, "int" );

        $subscribers = $this->getDbo()->setQuery($query, $this->limitstart, $this->limit)->loadObjectList();

        $this->total = $this->getDbo()->setQuery( "SELECT FOUND_ROWS()" )->loadResult ();

        if (! $subscribers)
        {
            return null;
        }

        foreach ($subscribers as $k=>&$member)
        {
            $member->url2object = JUri::base (true) . "/index.php?option=com_jlexcomment&view=items&layout=form&id=" . $member->obj_id;
            $member->url2author = JUri::base (true) . '/index.php?option=com_users&task=user.edit&id=' . $member->created_by;
            $member->url2edit = JUri::base (true) . "/index.php?option=com_jlexcomment&view=subscription&layout=form&id=" . $member->id;
        }

        return $subscribers;
    }


    public function getPageNav()
    {
        jimport('joomla.html.pagination');
        return new JPagination($this->total, $this->limitstart, $this->limit );
    }

    // for object single
    public $id = 0;

    public function getSubscriber()
    {
        if($this->id<1) return null;

        $query = $this->getDbo()->getQuery (true);
        $query->select("*")
              ->from("#__jlexcomment_subscribe")
              ->where("id=" . $this->id);

        $item = $this->getDbo()->setQuery($query)->loadObject();

        return $item;
    }

    public function getForm()
    {
        $path = dirname (__FILE__) . "/forms/subscription.xml";
        $form = JForm::getInstance("jcm_sub", $path);

        if($this->id>0)
        {
            $item = $this->getSubscriber();
            if(!$item)
            {
                throw new Exception( JText::_("JCM_PAGE_NOT_FOUND"), 404);
                return false;
            }

            $form->bind($item);
        }

        return $form;
    }

    public function save()
    {
        $app = JFactory::getApplication();
        $allows = [
            "obj_id"    => "int",
            "name"      => "string",
            "email"     => "string",
            "created_by"   => "int",
            "created_time" => "string"
        ];

        if($app->isClient("administrator"))
        {
            $allows["id"] = "int";
            $allows["email_confirmed"] = "int";
        }

        $data = $app->input->getArray($allows);
        $row  = $this->getTable("Subscribe", "TableCm");

        if(isset($data["id"]) && $data["id"]>0)
        {
            $row->load($data["id"]);
        }

        $row->bind($data);

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

    public $jcm_state = 0;

    public function state()
    {
        $app = JFactory::getApplication();
        $cid = array_key_exists('cid', $_POST) ? $_POST['cid'] : null;

        if (! is_array($cid) || ! count($cid)) 
        {
            throw new Exception(JText::_("JCM_NO_ROW_FOUND_TO_TASK"), 500);
        }

        $state = $this->jcm_state > 0 ? 1 : 0;

        $query = $this->_db->getQuery(true);
        $query->update("#__jlexcomment_subscribe")
              ->set("email_confirmed={$state}")
              ->where("id IN(".implode(",", $cid).")");

        // make query
        $this->_db->setQuery($query)->execute();

        return true;
    }

    public function switch_status($status=1)
    {
        $app = JFactory::getApplication();
        $cid = array_key_exists('cid', $_POST) ? $_POST['cid'] : null;

        if (! is_array($cid) || ! count($cid)) 
        {
            throw new Exception(JText::_("JCM_NO_ROW_FOUND_TO_TASK"), 500);
        }

        $query = $this->_db->getQuery(true);
        $query->update("#__jlexcomment_subscribe")
              ->set("published={$status}")
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

        $query = $this->_db->getQuery(true);
        $query->delete("#__jlexcomment_subscribe")
              ->where("id IN(".implode(",", $cid).")");
        
        // make query
        $this->_db->setQuery($query)->execute();

        return true;
    }
}
