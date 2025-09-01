<?php
/**
 * @package     JLex Comment
 * @version     1.0.0
 * @copyright   Copyright (C) 2013-2016 JLexArt Team (http://www.jlexart.com). All rights reserved.
 * @license     GNU/GPL or later
 * @author      JLexArt (support@jlexart.com)
 */

defined ( '_JEXEC' ) or die ();

jimport ("joomla.filesystem.file");
require_once dirname (__FILE__) . '/../libraries/class.image.php';

class JLexCommentModelUsers extends JModelLegacy
{
    protected $limit = 20;

    protected $limitstart = 0;

    protected $total = 0;

    public function getUsers()
    {
        $app = JFactory::getApplication();
        $whereClauses = array();

        $query = $this->getDbo()->getQuery(true);
        $query->select ("SQL_CALC_FOUND_ROWS u.*")
              ->from ("#__users AS u")

              ->select ("uc.*")
              ->leftJoin ("#__jlexcomment_users AS uc ON u.id=uc.userid")
              ->group ("u.id");

        // blacklist
        $query->select ("IF(bls.id>0,1,0) blocked")
              ->leftJoin ("#__jlexcomment_blacklist bls ON (u.id=bls.method_value AND bls.method=1)");

        // sub table
        $subTable = "(SELECT COUNT(*) count_cm, created_by id FROM #__jlexcomment WHERE created_by>0 GROUP BY created_by)";
        
        $query->select ("ucount.count_cm")
              ->leftJoin ("$subTable ucount ON u.id=ucount.id");

        // where clauses
        $keywords   = $app->getUserStateFromRequest ( "jcm.u.query", "filter_search", "");
    
        $this->setState('jcm.u.query', $keywords);

        if (!preg_match("/^\s*$/", $keywords))
        {
            $keywords = $this->getDbo()->quote("%" . $keywords . "%");
            $whereClauses[] = "(u.name LIKE $keywords || u.username LIKE $keywords || u.email LIKE $keywords)";
        }

        if (count($whereClauses) > 0)
        {
            $query->where ($whereClauses);
        }

        // sort by
        $sortBy = $app->getUserStateFromRequest ( "jcm.u.sortby", "filter_order", "u.registerDate");
        $sortDir = $app->getUserStateFromRequest ( "jcm.u.sortdir", "filter_order_Dir", "desc");

        $sortBy_allows = array ("u.registerDate", "u.name", "ucount.count_cm", "bls.id");
        $sortDir_allows = array ("desc", "asc");

        if (!array_key_exists($sortBy, $sortBy_allows))
        {
            $sortBy = "u.registerDate";
        }

        if (!array_key_exists($sortDir, $sortDir_allows))
        {
            $sortDir = "desc";
        }
        
        $this->setState('jcm.u.sortby', $sortBy);
        $this->setState('jcm.u.sortdir', $sortDir);

        $query->order( $sortBy . " " . $sortDir);

        $this->limitstart = $app->getUserStateFromRequest ( "jcm.u.limitstart", 'limitstart', 0, "int" );
        $this->limit = $app->getUserStateFromRequest ("jcm.u.limit", 'limit', $this->limit, 20, "int" );

        $users = $this->getDbo()->setQuery($query, $this->limitstart, $this->limit)->loadObjectList();

        $this->total = $this->getDbo()->setQuery( "SELECT FOUND_ROWS()" )->loadResult ();

        if (! $users)
        {
            return null;
        }

        foreach ($users as $k=>&$user)
        {
            $user->count_cm = intval ($user->count_cm);
            $user->url2edit = JUri::base (true) . '/index.php?option=com_users&task=user.edit&id=' . $user->id;
            if (!empty($user->auth_picture))
            {
                $user->thumbnail = preg_match ("/^http(s)?:\/\//", $user->auth_picture) ? $user->auth_picture : JUri::root(true) . "/media/jcm/avatar/" . $user->auth_picture;
            } else {
                $user->thumbnail = null;
            }
        }

        return $users;
    }


    public function getPageNav() {
        jimport ( 'joomla.html.pagination' );
        return new JPagination ( $this->total, $this->limitstart, $this->limit );
    }

    public function upload ()
    {
        $app     = JFactory:: getApplication ();
        $uid     = $app->input->getInt ("id", 0);
        $time    = JFactory::getDate()->toSql();

        if ($uid < 1)
        {
            $this->setError (JText::_("JCM_USER_NOT_FOUND"));
            return false;
        }

        $fileAllows = array(
                'jpg', // image type
                'jpeg',
                'png',
                'gif'
            );

        $file = array_key_exists('file', $_FILES) ? $_FILES['file'] : null;

        if ($file == null || $file['size'] == 0 || $file['error'] != 0)
        {
            $this->setError( JText::_("JCM_SELECT_FILE_TO_UPLOAD") );
            return false;
        }

        $type = strtolower( JFile::getExt ($file['name']) );

        if (! in_array($type, $fileAllows))
        {
            $this->setError( JText::_("JCM_FILE_TYPE_NOT_ALLOWED") );
            return false;
        }

        // assign some parameters
        $filename_new = substr(md5 ($file ['name'] . $time . $uid), 0, 16) . '.' . $type;

        if ( ! @is_array(getimagesize($file['tmp_name'])) )
        {
            $this->setError (JText::_("JCM_FILE_NOT_IMAGE"));
            return false;
        }

        // upload tmp file
        /*$data ['path'] = 'media/jcm/images/' . $filename_new;
        $dest = JPATH_ROOT . '/' . $data['path'];

        if (! JFile::upload ($file['tmp_name'], $dest) )
        {
            $this->setError ("Upload file error.");
            return false;
        }*/

        // create thumbnail image
        $resizeObj = new abeautifulsite\SimpleImage ($file['tmp_name']);

        try {
            $thumb = JPATH_ROOT . '/media/jcm/avatar/' . $filename_new;
            $resizeObj->thumbnail (120, 120)
                        ->save ($thumb);
        } catch (Exception $e) {
            $this->setError ($e->getMessage());
            return false;
        }

        $query = "SELECT * FROM #__jlexcomment_users WHERE userid=" . $uid;
        $user  = $this->getDbo()->setQuery ($query, 0, 1)->loadObject ();

        if (! $user)
        {
            $row = $this->getTable ("User", "TableCm");
            $row->bind (array(
                    "userid"    => $uid,
                    "created"   => $time,
                    "auth"      => "joomla",
                    "auth_picture" => $filename_new,
                ));

            if(!$row->store())
            {
                echo $row->getError();
                die;
            }
        } else {
            // delete old avatar
            if (! preg_match("/^http(s)?:\/\//", $user->auth_picture))
            {
                $old = JPATH_ROOT . "/media/jcm/avatar/" . $user->auth_picture;
                if (JFile::exists ($old))
                {
                    JFile::delete ($old);
                }
            }

            $query = "UPDATE #__jlexcomment_users SET auth_picture=" . $this->getDbo()->quote ($filename_new);
                $query.= "\nWHERE userid=" . $uid;

            $this->getDbo()->setQuery ($query)->execute();
        }

        return true;
    }

    public function add2bl ()
    {
        $app    = JFactory::getApplication();
        $cid    = array_key_exists('cid', $_POST) ? $_POST['cid'] : null;
        $time   = JFactory::getDate()->toSql ();
        $user   = JFactory::getUser ();

        if (! is_array($cid) || ! count($cid)) 
        {
            throw new Exception( JText::_("JCM_NO_ROW_FOUND_TO_TASK"), 500 );
            return false;
        }

        $row = $this->getTable ("blacklist", "TableCm");

        foreach ($cid as $id)
        {
            $row->set ('id', 0);
            $row->load (array(
                    'method'=>1,
                    'method_value' => $id
                ));

            if ($row->id)
            {
                // row exist
                continue;
            }

            $row->bind (array(
                    'method'        => 1,
                    'method_value'  => $id,
                    'created_by'    => $user->id,
                    'created_time'  => $time
                ));

            $row->store ();
        }

        return true;
    }

    public function outbl ()
    {
        $app    = JFactory::getApplication();
        $cid    = array_key_exists('cid', $_POST) ? $_POST['cid'] : null;

        if (! is_array($cid) || ! count($cid)) 
        {
            throw new Exception( JText::_("JCM_NO_ROW_FOUND_TO_TASK"), 500);
            return false;
        }

        $query = "DELETE FROM #__jlexcomment_blacklist WHERE method=1 AND method_value IN (" .implode(",", $cid). ")";
        $this->getDbo()->setQuery ($query)->execute();

        return true;
    }

    public function clear_thumb ()
    {
        set_time_limit (0);

        $app    = JFactory::getApplication();
        $cid    = array_key_exists('cid', $_POST) ? $_POST['cid'] : null;

        if (! is_array($cid) || ! count($cid)) 
        {
            throw new Exception( JText::_("JCM_NO_ROW_FOUND_TO_TASK"), 500);
            return false;
        }

        $query = "UPDATE #__jlexcomment_users SET auth_picture='' WHERE userid IN (" .implode(",", $cid). ")";
    
        $this->getDbo()->setQuery ($query)->execute();

        return true;
    }

    public function clear_comment ()
    {
        set_time_limit (0);

        $app    = JFactory::getApplication();
        $cid    = array_key_exists('cid', $_POST) ? $_POST['cid'] : null;

        if (! is_array($cid) || ! count($cid)) 
        {
            throw new Exception( JText::_("JCM_NO_ROW_FOUND_TO_TASK"), 500 );
            return false;
        }

        $query = "DELETE FROM #__jlexcomment WHERE created_by IN (" .implode(",", $cid). ")";
    
        $this->getDbo()->setQuery ($query)->execute();

        return true;
    }
}
