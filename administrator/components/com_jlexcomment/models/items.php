<?php
/**
 * @package     JLex Comment
 * @version     1.0.0
 * @copyright   Copyright (C) 2013-2016 JLexArt Team (http://www.jlexart.com). All rights reserved.
 * @license     GNU/GPL or later
 * @author      JLexArt (support@jlexart.com)
 */

defined ( '_JEXEC' ) or die ();

class JLexCommentModelItems extends JModelLegacy
{
    public $limit = 20;

    public $limitstart = 0;

    public $total = 0;

    public function getObjects()
    {
        $app    = JFactory::getApplication();
        $config = JLexCommentHelper::getConfig ();
        $wClauses = [];

        $query = $this->_db->getQuery(true);
        $query->select("SQL_CALC_FOUND_ROWS obj.*")
              ->from("#__jlexcomment_obj obj")
              ->select("u.name AS author")
              ->leftJoin("#__users AS u ON obj.created_by=u.id");

        // where clauses
        $keywords = $app->getUserStateFromRequest("jcm.items.q", "q", "");
        $object = $app->getUserStateFromRequest("jcm.items.component", "filter_component", "");

        if(!preg_match("/^\s*$/", $keywords))
        {
            $keywords = $this->_db->quote("%" . $keywords . "%");
            $wClauses[] = "obj.title LIKE $keywords";
        }

        if(!preg_match("/^\s*$/", $object))
        {
            $object = $this->_db->quote("%" . $object . "%");
            $wClauses[] = "obj.com_name LIKE $object";
        }

        if(count($wClauses)>0) $query->where($wClauses);

        // sort by
        $sortBy = $app->getUserStateFromRequest("jcm.items.sortby", "filter_order", "obj.created_time");
        $sortDir = $app->getUserStateFromRequest("jcm.items.sortdir", "filter_order_Dir", "desc");

        // safe
        $sortDir = $sortDir=="desc" ? "desc" : "asc";
        $sortByColumns = [
                "obj.created_time",
                "obj.title",
                "obj.created_by",
                "obj.cm_count",
                "obj.url",
                "obj.published"
            ];

        if(!in_array($sortBy, $sortByColumns))
        {
            $sortBy = $sortByColumns[0];
        }

        $query->order($sortBy . " " . $sortDir);

        $this->limitstart = $app->getUserStateFromRequest("jcm.items.limitstart", 'limitstart', 0, "int");
        $this->limit = $app->getUserStateFromRequest ("jcm.items.limit", 'limit', $this->limit, 20, "int");

        $objects = $this->_db->setQuery($query, $this->limitstart, $this->limit)->loadObjectList();

        $this->total = (int) $this->_db->setQuery("SELECT FOUND_ROWS()")->loadResult();

        if(!$objects) return null;

        foreach($objects as $k=>&$obj)
        {
            if($config->get("cm_link",0)==1)
            {
                $router = CommentHelperAdmin::getObjDetail($obj->com_name, $obj->com_key, $obj->title, $obj->url);
                $obj->title = $router->title;
                $obj->url_preview = $router->url;
                $obj->url = $obj->url_preview;
            } else {
                $obj->url_preview = JUri::root(true) . "/" . ltrim($obj->url, "/");
            }

            $obj->author = !$obj->author ? "Bot" : $obj->author;

            $params = new JRegistry();
            if(!empty($obj->params) && json_decode($obj->params))
            {
                $params->loadObject(json_decode($obj->params));
            }
            $obj->params = $params;

            $obj->url2edit = JUri::base(true).'/index.php?option=com_jlexcomment&view=items&layout=form&id=' . $obj->id;
            $obj->url2cms  = JUri::base(true).'/index.php?option=com_jlexcomment&view=comments&oid=' . $obj->id;
        }

        return $objects;
    }

    public function getComponents()
    {
        $query = $this->_db->getQuery(true);
        $query->select ("com_name")
              ->from ("#__jlexcomment_obj")
              ->group ("com_name");

        $comps = $this->_db->setQuery($query)->loadObjectList();

        if(!$comps) return null;

        foreach($comps as $k=>&$com)
        {
            $com->value = $com->com_name;
            $com->text = ucfirst($com->com_name);

            unset ($com->com_name);
        }

        $option = new stdClass();
        $option->value = "";
        $option->text  = "- Select component -";

        array_unshift($comps, $option);

        return $comps;
    }

    public function getPageNav()
    {
        jimport("joomla.html.pagination");
        return new JPagination($this->total, $this->limitstart, $this->limit);
    }

    // for object single
    public $id = 0;

    public function getObject()
    {
        if($this->id<1) return null;

        $config = JLexCommentHelper::getConfig();
        $query  = $this->_db->getQuery(true);
        
        $query->select("*")
              ->from("#__jlexcomment_obj obj")
              ->where("obj.id=".$this->_db->quote($this->id));

        $row = $this->_db->setQuery($query)
                            ->loadObject();

        if(!$row) return null;

        $row->params = json_decode($row->params);
        
        if($config->get("cm_link", 0)==1)
        {
            $router = CommentHelperAdmin::getObjDetail($row->com_name, $row->com_key, $row->title, $row->url);
            $row->title = $router->title;
            $row->url   = $router->url;
        }

        return $row;
    }

    public function getForm()
    {
        $path = dirname (__FILE__)."/forms/object.xml";
        $form = JForm::getInstance("jcm_object", $path);

        if($this->id>0)
        {
            $row = $this->getObject();
            if(!$row)
            {
                throw new Exception(JText::_("JCM_PAGE_NOT_FOUND"), 404);
            }

            $form->bind(['jform'=> $row]);
        }

        return $form;
    }

    public function save()
    {
        $app  = JFactory::getApplication();
        $data = $app->input->get("jform", [], "array");

        $row  = $this->getTable("Object", "TableCm");
        $row->bind($data, ["cm_count", "cm_count_active", "cm_i_count", "cm_i_count_active"]);

        if(!$row->check())
        {
            $this->setError ($row->getError());
            return false;
        }

        if(!$row->store())
        {
            $this->setError($row->getError());
            return false;
        }

        return $row->id;
    }

    public $jcm_state = 0;

    public function state()
    {
        $app    = JFactory::getApplication();
        $cid    = array_key_exists('cid', $_POST) ? $_POST['cid'] : null;

        if(!is_array($cid) || !count($cid)) 
        {
            throw new Exception(JText::_("JCM_NO_ROW_FOUND_TO_TASK"), 500);
        }

        $state = $this->jcm_state>0?1:0;

        $query = $this->_db->getQuery(true);
        $query->update("#__jlexcomment_obj")
              ->set("published={$state}")
              ->where("id IN (".implode(",", $cid).")");

        $this->_db->setQuery($query)->execute();
        return true;
    }

    public function remove()
    {   
        $row = $this->getTable('Object', 'TableCm');
        $cid = array_key_exists('cid', $_POST) ? $_POST['cid'] : null;

        if(!is_array($cid) || !count($cid)) 
        {
            throw new Exception(JText::_("JCM_NO_ROW_FOUND_TO_TASK"), 500);
        }

        $result = [
                'total' => count($cid),
                'success' => 0
            ];

        foreach ($cid as $k=>$id)
        {
            $row->set('id', 0); // reset
            $row->load ($id);
            if ($row->delete())
            {
                $result['success']++;
            }
        }

        return $result;
    }

    public function truncate()
    {
        $app = JFactory::getApplication();
        $cid = array_key_exists('cid', $_POST) ? $_POST['cid'] : null;

        if(!is_array($cid) || !count($cid)) 
        {
            throw new Exception(JText::_("JCM_NO_ROW_FOUND_TO_TASK"), 500);
        }

        $query = $this->_db->getQuery(true);
        $query->update("#__jlexcomment_obj")
              ->set(["cm_count=0", "cm_count_active=0"])
              ->where("id IN (".implode(",", $cid).")");

        $this->_db->setQuery($query)->execute();

        // delete comment
        $query->clear()
              ->delete("#__jlexcomment")
              ->where("obj_id IN (".implode(",", $cid).")");
        $this->_db->setQuery($query)->execute();
        
        return true;
    }

    public function recalculate()
    {
        $app    = JFactory::getApplication();
        $cid    = array_key_exists('cid', $_POST) ? $_POST['cid'] : null;

        if(!is_array($cid) || !count($cid)) 
        {
            throw new Exception(JText::_("JCM_NO_ROW_FOUND_TO_TASK"), 500);
        }

        $query = $this->_db->getQuery(true);
        $query->select("COUNT(id) cm_count")
              ->select("SUM(IF(published=1,1,0)) cm_count_active")
              ->select("SUM(IF(parent_id=0,1,0)) cm_i_count")
              ->select("SUM(IF(parent_id=0 AND published=1,1,0)) cm_i_count_active")
              ->select("obj_id")
              ->from ("#__jlexcomment")
              ->where ("obj_id IN (".implode(",", $cid).")")
              ->group ("obj_id");

        $items = $this->_db->setQuery($query)->loadObjectList();

        if(count($items))
        {
            foreach ($items as $item)
            {
                $query = "UPDATE #__jlexcomment_obj SET ";
                    $query.= $this->_db->quoteName("cm_count") . "=" . $this->_db->quote($item->cm_count);
                    $query.= "," . $this->_db->quoteName("cm_count_active") . "=" . $this->_db->quote($item->cm_count_active);
                    $query.= "," . $this->_db->quoteName("cm_i_count") . "=" . $this->_db->quote($item->cm_i_count);
                    $query.= "," . $this->_db->quoteName("cm_i_count_active") . "=" . $this->_db->quote($item->cm_i_count_active);
                    $query.= "\nWHERE id=" . $item->obj_id;
                $this->_db->setQuery($query)->execute();
            }
        }
        
        return true;
    }
}
