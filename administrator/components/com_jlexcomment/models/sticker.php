<?php
/**
 * @package     JLex Comment
 * @version     1.0.0
 * @copyright   Copyright (C) 2013-2016 JLexArt Team (http://www.jlexart.com). All rights reserved.
 * @license     GNU/GPL or later
 * @author      JLexArt (support@jlexart.com)
 */

defined ( '_JEXEC' ) or die ();

// using to create thumb image
require_once dirname (__FILE__).'/../libraries/class.image.php';
jimport('joomla.filesystem.file');

class JLexCommentModelSticker extends JModelLegacy
{
    public $id = 0;

    public $group_id = 0;

    public function upload()
    {
        $app    = JFactory::getApplication();
        $row    = $this->getTable('sticker', 'TableCm');
        $post   = $app->input->post;
        $user   = JFactory::getUser();
        $now    = JFactory::getDate()->toSql();
        $config = JLexCommentHelper::getConfig();

        if($app->isClient('site'))
        {
            $this->group_id = 0;
            if(!$config->get('sticker', 1) || !$config->get('sticker_photo', 1))
            {
                $this->setError(JText::_("JCM_THIS_FEATURE_IS_DISABLED"));
                return fale;
            }
        } else {
            if($this->group_id==-1)
            {
                $this->setError(JText::_("JCM_YOU_MUST_SELECT_STICKER_GROUP"));
                return false;
            }
        }

        if($app->isClient('administrator') && $this->id>0)
        {
            $row->load($this->id);
        }

        // upload
        $file = array_key_exists('file', $_FILES) ? $_FILES['file'] : null;
        if($file && $file['size']>0)
        {
            $type = strtolower(JFile::getExt ($file['name']));
            $allows = array('jpeg','jpg','png','gif');

            if(!in_array($type, $allows))
            {
                $this->setError (JText::_("JCM_FILE_TYPE_NOT_ALLOWED"));
                return false;
            }

            if(!@is_array(getimagesize($file['tmp_name'])))
            {
                $this->setError(JText::_("JCM_FILE_MUST_IMAGE"));
                return false;
            }

            $row->set('caption', JFile::makeSafe($file['name']));

            $filename_new = substr(md5 ($file ['name'] . $now . $user->id), 0, 16) . '.' . $type;

            $path = 'media/jcm/stickers/'.$filename_new;
            $dest = JPATH_ROOT.'/'.$path;

            $row->set('path2file', $path);

            if($type=='gif')
            {
                if(!JFile::upload($file['tmp_name'], $dest))
                {
                    $this->setError (JText::_("JCM_UPLOAD_FILE_ERROR"));
                    return false;
                }
            } else {
                // fix_orientation
                // create thumbnail image
                $resizeObj = new abeautifulsite\SimpleImage($file['tmp_name']);
                try {
                    $ms = (int) $config->get('sticker_photo_sz', 300);
                    if($ms<100) $ms=100;

                    $resizeObj->auto_orient()->best_fit($ms, $ms)
                                ->save($dest);
                } catch (Exception $e) {
                    $this->setError($e->getMessage());
                    return false;
                }
            }
        }

        if($app->isClient('administrator'))
        {
            $allows = [
                'id' => 'int',
                'caption' => 'string',
                'created_time' => 'string',
                'published' => 'int',
                'created_by' => 'int',
                'group_id' => 'int'
            ];

            $row->bind($post->getArray($allows), ["path2file"]);
        }

        if($row->created_by<1)
            $row->set("created_by", $user->id);

        if(empty($row->created_time))
            $row->set("created_time", $now);

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

        return $row;
    }

    protected $limit = 20;

    protected $limitstart = 0;

    protected $total = 0;

    public function getStickers()
    {
        $app = JFactory::getApplication();
        $whereClauses = array();

        $query = $this->getDbo()->getQuery(true);
        $query->select ("SQL_CALC_FOUND_ROWS s.*")
              ->from ("#__jlexcomment_sticker s")

              ->select ("sg.name group_name")
              ->leftJoin ("#__jlexcomment_sticker_group sg ON s.group_id=sg.id")

              ->select ("u.name AS author")
              ->leftJoin ("#__users AS u ON s.created_by=u.id");

        // where clauses
        $keywords       = $app->getUserStateFromRequest ( "jcm.sk.query", "filter_search", "");
        $publish_state  = $app->getUserStateFromRequest ( "jcm.sk.publish", "filter_state", "-1");
        $group_id       = $app->getUserStateFromRequest ( "jcm.sk.group", "filter_group", "-1", "int");

        $this->setState('jcm.sk.query', $keywords);
        $this->setState('jcm.sk.publish', $publish_state);
        $this->setState('jcm.sk.group', $group_id);

        if (!preg_match("/^\s*$/", $keywords))
        {
            $keywords = $this->getDbo()->quote("%" . $keywords . "%");
            $whereClauses[] = "(s.caption LIKE $keywords)";
        }

        if ($publish_state!=-1)
        {
            $whereClauses[] = "s.published=".$publish_state;
        }

        if ($group_id==-1)
        {
            $whereClauses[] = "s.group_id!=0";
        } else {
            $whereClauses[] = "s.group_id=" . $group_id;
        }



        if (count($whereClauses) > 0)
        {
            $query->where ($whereClauses);
        }

        // sort by
        $sortBy = $app->getUserStateFromRequest ( "jcm.s.sortby", "filter_order", "s.created_time");
        $sortDir = $app->getUserStateFromRequest ( "jcm.s.sortdir", "filter_order_Dir", "desc");

        // safe
        $sortDir = $sortDir=="desc" ? "desc" : "asc";
        $sortByColumns = array (
                "s.created_time",
                "s.caption",
                "s.group_id",
                "s.created_by",
                "s.published"
            );

        if (!in_array($sortBy, $sortByColumns))
        {
            $sortBy = $sortByColumns[0];
        }

        $this->setState('jcm.s.sortby', $sortBy);
        $this->setState('jcm.s.sortdir', $sortDir);

        $query->order( $sortBy . " " . $sortDir);

        $this->limitstart = $app->getUserStateFromRequest ( "jcm.s.limitstart", 'limitstart', 0, "int" );
        $this->limit = $app->getUserStateFromRequest ("jcm.s.limit", 'limit', $this->limit, 20, "int" );

        $stickers = $this->getDbo()->setQuery($query, $this->limitstart, $this->limit)->loadObjectList();

        $this->total = $this->getDbo()->setQuery( "SELECT FOUND_ROWS()" )->loadResult ();

        if (! $stickers)
        {
            return null;
        }

        foreach ($stickers as $k=>&$sticker)
        {
            $sticker->url2edit = JUri::base (true) . '/index.php?option=com_jlexcomment&view=sticker&layout=form&id=' . $sticker->id;
            $sticker->preview  = JUri::root (true) . '/' . $sticker->path2file;
            $sticker->url2author = JUri::base (true) . '/index.php?option=com_users&task=user.edit&id=' . $sticker->created_by;
            $sticker->url2group = JUri::base (true) . '/index.php?option=com_jlexcomment&view=sticker&filter_group=' . $sticker->group_id;

            if (! $sticker->group_id)
            {
                $sticker->group_name = "User";
            }
        }

        return $stickers;
    }

    public function getPageNav() {
        jimport ( 'joomla.html.pagination' );
        return new JPagination ( $this->total, $this->limitstart, $this->limit );
    }

    // for object single
    public function getSticker ()
    {
        if ( $this->id < 1 )
        {
            return null;
        }

        $query = $this->getDbo()->getQuery (true);
        $query->select ("*")
              ->from ("#__jlexcomment_sticker")
              ->where ("id=" . $this->id);

        $sticker = $this->getDbo()->setQuery ($query)->loadObject ();

        if ($sticker)
        {
            $sticker->preview = JUri::root (true) . '/' . $sticker->path2file;
        }

        return $sticker;
    }

    public function getForm ()
    {
        $path = dirname (__FILE__) . "/forms/sticker.xml";
        $form = JForm::getInstance("jcm_sticker", $path);

        if ($this->id > 0)
        {
            $sticker = $this->getSticker ();
            if ( !$sticker)
            {
                throw new Exception( JText::_("JCM_PAGE_NOT_FOUND"), 404);
                return false;
            }

            $form->bind ( $sticker );

            // unset required attribute
            $form->setFieldAttribute ("file", "required", false);
        }

        return $form;
    }

    public function load_group ()
    {
        $app = JFactory::getApplication ();
        $group_query = $app->input->getString ('query', '');

        $query = $this->getDbo()->getQuery (true);
        $query->select ('*')
              ->from ('#__jlexcomment_sticker')
              ->where ('group_id > 0');

        if ( $app->isClient('site') )
        {
            $query->where ('published=1');

            // filter: group id && query
            if ($this->group_id > 0)
            {
                $query->where ('group_id=' . $this->group_id);
            }
        } else {
            // request group_id
            if ( $this->group_id < 1)
            {
                throw new Exception( JText::_("JCM_STICKER_GROUP_NOT_FOUND"), 404 );
                return false;
            }

            $row = $this->getTable ('StickerGroup', 'TableCm');
            $row->load ($this->group_id);

            if (! $row->id || ($app->isClient('site') && $row->published==0) )
            {
                throw new Exception( JText::_("JCM_STICKER_GROUP_NOT_FOUND"), 404 );
                return false;
            }
        }

        if (!preg_match('/^\s*$/', $group_query))
        {
            $querySafe = $this->getDbo ()->quote ('%' . $group_query . '%');
            $query->where ('caption LIKE ' . $querySafe);
        }              

        $stickers = $this->getDbo()->setQuery ($query)->loadObjectList ();

        foreach ($stickers as $k=>&$sticker)
        {
            $sticker->url = JUri::root (true) . '/' . $sticker->path2file;
            if ($app->isClient('site'))
            {
                unset ($sticker->path2file);
                unset ($sticker->created_by);
                unset ($sticker->created_time);
                unset ($sticker->published);
                unset ($sticker->auto_post);
            }
        }

        return $stickers;
    }

    public function list_group ()
    {
        $app = JFactory::getApplication ();

        $query = $this->getDbo()->getQuery (true);
        $query->select ('*')
              ->from ('#__jlexcomment_sticker_group');

        if ($app->isClient('site'))
        {
            $query->where ('published=1');
        }

        $list = $this->getDbo()->setQuery ($query)->loadObjectList();

        return $list;
    }

    public function remove ()
    {
        $row = $this->getTable('Sticker', 'TableCm');
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
            $row->set('id', 0); // reset
            $row->load ($id);
            if ($row->delete())
            {
                $result['success']++;
            }
        }

        return $result;
    }

    public $jcm_state = 1;

    public function state ()
    {
        $app    = JFactory::getApplication();
        $cid    = array_key_exists('cid', $_POST) ? $_POST['cid'] : null;

        if (! is_array($cid) || ! count($cid)) 
        {
            throw new Exception( JText::_("JCM_NO_ROW_FOUND_TO_TASK"), 500);
            return false;
        }

        $state = $this->jcm_state > 0 ? 1 : 0;

        $query = "UPDATE #__jlexcomment_sticker SET published=$state";
            $query.= "\nWHERE id IN (" .implode(",", $cid). ")";

        // make query
        $this->getDbo()->setQuery($query)->execute();

        return true;
    }
}
