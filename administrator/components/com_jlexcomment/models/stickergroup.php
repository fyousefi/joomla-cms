<?php
/**
 * @package     JLex Comment
 * @version     1.0.0
 * @copyright   Copyright (C) 2013-2016 JLexArt Team (http://www.jlexart.com). All rights reserved.
 * @license     GNU/GPL or later
 * @author      JLexArt (support@jlexart.com)
 */

defined ( '_JEXEC' ) or die ();

class JLexCommentModelStickerGroup extends JModelLegacy
{
    protected $limit = 20;

    protected $limitstart = 0;

    protected $total = 0;

    public function getStickerGroups()
    {
        $app = JFactory::getApplication();
        $whereClauses = array();

        $query = $this->getDbo()->getQuery(true);
        $query->select ("SQL_CALC_FOUND_ROWS s.*")
              ->from ("#__jlexcomment_sticker_group s")
              ->select ("u.name AS author")
              ->leftJoin ("#__users AS u ON s.created_by=u.id");

        // sub table
        $table = "(SELECT group_id, COUNT(*) count_sticker FROM #__jlexcomment_sticker WHERE group_id>0 GROUP BY group_id)";
        $query->select ("gc.count_sticker")
              ->leftJoin ("{$table} gc ON s.id=gc.group_id");

        // where clauses
        $keywords   = $app->getUserStateFromRequest ( "jcm.sg.query", "filter_search", "");
        $publish_state = $app->getUserStateFromRequest ( "jcm.sg.publish", "published", "-1");

        $this->setState('jcm.sg.query', $keywords);
        $this->setState('jcm.sg.publish', $publish_state);

        if (!preg_match("/^\s*$/", $keywords))
        {
            $keywords = $this->getDbo()->quote("%" . $keywords . "%");
            $whereClauses[] = "(s.name LIKE $keywords OR s.description LIKE $keywords)";
        }

        if ($publish_state!=-1)
        {
            $whereClauses[] = "s.published=".$publish_state;
        }

        if (count($whereClauses) > 0)
        {
            $query->where ($whereClauses);
        }

        // sort by
        $sortBy = $app->getUserStateFromRequest ( "jcm.sg.sortby", "filter_order", "s.created_time");
        $sortDir = $app->getUserStateFromRequest ( "jcm.sg.sortdir", "filter_order_Dir", "desc");

        // safe
        $sortDir = $sortDir=="desc" ? "desc" : "asc";
        $sortByColumns = array (
                "s.created_time",
                "s.name",
                "s.created_by",
                "s.published",
                "gc.count_sticker"
            );

        if (!in_array($sortBy, $sortByColumns))
        {
            $sortBy = $sortByColumns[0];
        }

        $this->setState('jcm.sg.sortby', $sortBy);
        $this->setState('jcm.sg.sortdir', $sortDir);

        $query->order( $sortBy . " " . $sortDir);

        $this->limitstart = $app->getUserStateFromRequest ( "jcm.sg.limitstart", 'limitstart', 0, "int" );
        $this->limit = $app->getUserStateFromRequest ("jcm.sg.limit", 'limit', $this->limit, 20, "int" );

        $groups = $this->getDbo()->setQuery($query, $this->limitstart, $this->limit)->loadObjectList();

        $this->total = $this->getDbo()->setQuery( "SELECT FOUND_ROWS()" )->loadResult ();

        if (! $groups)
        {
            return null;
        }

        foreach ($groups as $k=>&$group)
        {
            $group->url2edit = JUri::base (true) . '/index.php?option=com_jlexcomment&view=stickergroup&layout=form&id=' . $group->id;
            $group->count_sticker = is_numeric($group->count_sticker) ? $group->count_sticker : 0;
            $group->url2author = JUri::base (true) . '/index.php?option=com_users&task=user.edit&id=' . $group->created_by;
        }

        return $groups;
    }

    public function getPageNav()
    {
        jimport('joomla.html.pagination');
        return new JPagination( $this->total, $this->limitstart, $this->limit );
    }

    // for object single
    public $id = 0;

    public function getStickerGroup()
    {
        if($this->id<1) return null;

        $query = $this->_db->getQuery (true);
        $query->select("*")
              ->from("#__jlexcomment_sticker_group")
              ->where("id=" . $this->id);

        $group = $this->_db->setQuery($query)->loadObject();

        return $group;
    }

    public function getForm()
    {
        $path = dirname(__FILE__)."/forms/sticker_group.xml";
        $form = JForm::getInstance("jcm_sticker_group", $path);

        if($this->id>0)
        {
            $group = $this->getStickerGroup();
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
        $app  = JFactory::getApplication ();
        $data = $app->input->getArray([
                'jform' => [
                    'name' => 'string',
                    'description' => 'string',
                    'published' => 'int'
                ]
            ]);

        $row = $this->getTable("StickerGroup", "TableCm");
        $row->bind($data['jform']);

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

    public function remove ()
    {
        set_time_limit (0);
        jimport ("joomla.filesystem.file");

        $cid    = array_key_exists('cid', $_POST) ? $_POST['cid'] : null;

        if (! is_array($cid) || ! count($cid)) 
        {
            throw new Exception( JText::_("JCM_NO_ROW_FOUND_TO_TASK"), 500 );
            return false;
        }

        $result = array(
                'total' => count($cid),
                'success' => 0
            );

        $query = $this->getDbo()->getQuery (true);

        foreach ($cid as $k=>$id)
        {
            if ($id==0)
            {
                continue;
            }

            $query->clear ()
                  ->select ("path2file")
                  ->from ("#__jlexcomment_sticker")
                  ->where ("group_id=" . $id);

            $stickers = $this->getDbo()->setQuery($query)->loadObjectList ();
            if ($stickers)
            {
                foreach ($stickers as $icon)
                {
                    $file = JPATH_ROOT . "/" . $icon->path2file;
                    if (JFile::exists($file))
                    {
                        JFile::delete ($file);
                    }
                }

                $subQuery = "DELETE FROM #__jlexcomment_sticker WHERE group_id=" . $id;
                $this->getDbo()->setQuery ($query)->execute();
            }

            $result["success"] += 1;
        }

        $subQuery = "DELETE FROM #__jlexcomment_sticker_group";
            $subQuery.= "\nWHERE id IN (" .implode(",", $cid). ")";

        $this->getDbo()->setQuery ($subQuery)->execute ();

        return $result;
    }

    public $jcm_state = 1;

    public function state ()
    {
        $app    = JFactory::getApplication();
        $cid    = array_key_exists('cid', $_POST) ? $_POST['cid'] : null;

        if (! is_array($cid) || ! count($cid)) 
        {
            throw new Exception( JText::_("JCM_NO_ROW_FOUND_TO_TASK"), 500 );
            return false;
        }

        $state = $this->jcm_state > 0 ? 1 : 0;

        $query = "UPDATE #__jlexcomment_sticker_group SET published=$state";
            $query.= "\nWHERE id IN (" .implode(",", $cid). ")";

        // make query
        $this->getDbo()->setQuery($query)->execute();

        return true;
    }
}
