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
require_once dirname (__FILE__) . '/../libraries/class.image.php';
jimport('joomla.filesystem.file');

class JLexCommentModelMedia extends JModelLegacy
{
    public $id = 0;

    private function _isOwn()
    {
        if($this->id<1)
        {
            $this->setError (JText::_("JCM_PERMISSION_DENIED"));
            return false;
        }

        $session    = JFactory::getSession ();
        $user       = JFactory::getUser();
        $permission = $session->get('item_' . $this->id, false, 'jcm_media');

        if($permission==true) return true;


        $query = $this->_db->getQuery(true);
        $query->select('COUNT(m.id)')
              ->from('#__jlexcomment_media m')
              ->innerJoin('#__jlexcomment cm ON m.comment_id=cm.id')
              ->where([
                    "m.id=" . $this->_db->quote($this->id),
                    "m.created_by=".$this->_db->quote($user->id)
                ]);

        $return = $this->_db->setQuery($query)->loadResult();

        return $return>0?true:false;
    }

    public function upload()
    {
        $app     = JFactory::getApplication();
        $session = JFactory::getSession();
        $user    = JFactory::getUser();
        $time    = JFactory::getDate()->toSql();
        $data    = [
                "created_by" => $user->id,
                "created"    => $time
            ];

        $config  = JLexCommentHelper::getConfig ();

        $pathOg  = 'media/jcm';
        $pathTmp = trim($config->get('media_base'));
        $pathTmp = trim($pathTmp, '/');

        if(!preg_match('/^\s*$/', $pathTmp))
        {
            $pathOg = $pathTmp;
        }

        if($app->isClient('site') && $config->get("u_upload_file",false)==false)
        {
            $this->setError (JText::_("JCM_THIS_FEATURE_IS_DISABLED"));
            return false;
        }

        $fileAllows = $config->get("media_filetype", "jpg,jpeg,png,gif,zip,rar");
        $fileAllows = explode ("," , $fileAllows);

        $file = array_key_exists('file', $_FILES) ? $_FILES['file'] : null;

        if($file == null || $file['size'] == 0 || $file['error'] != 0)
        {
            $this->setError( JText::_("JCM_SELECT_FILE_TO_UPLOAD") );
            return false;
        }

        $max_file_size = $config->get("media_filesize",1);
        if($file['size'] > $max_file_size*1024*1024)
        {
            $this->setError( JText::sprintf("JCM_MAX_FILE_SIZE_ALLOW_IS",$max_file_size));
            return false;
        }

        $type = strtolower(JFile::getExt($file['name']));

        if(!in_array($type, $fileAllows))
        {
            $this->setError(JText::_("JCM_FILE_NOT_SUPPORTED"));
            return false;
        }

        // assign some parameters
        $data['name'] = $file['name'];
        $data['fileSize'] = $file['size'];

        $filename_new = substr(md5($file['name'] . $time . $user->id), 0, 16) . '.' . $type;
        $data['fileName'] = $filename_new;

        switch ($type)
        {
            case 'png':
            case 'jpg':
            case 'jpeg':
            case 'gif':
                $data ['fileType'] = 'image';
                $imginfo = @getimagesize($file['tmp_name']);
                if(!@is_array($imginfo))
                {
                    $this->setError (JText::_("JCM_FILE_NOT_IMAGE"));
                    return false;
                }

                // upload tmp file
                $data['path'] = $pathOg . '/og/' . $filename_new;
                $dest = JPATH_ROOT . '/' . $data['path'];

                if (! JFile::upload ($file['tmp_name'], $dest) )
                {
                    $this->setError (JText::_("JCM_UPLOAD_FILE_ERROR"));
                    return false;
                }

                // resize image if too large
                $photoMaxSize = $config->get ("media_photosize",1000);
                if(!preg_match('/^[1-9][0-9]*$/', $photoMaxSize) || $photoMaxSize<300) $photoMaxSize=1000;
                
                if($imginfo[0]>$photoMaxSize || $imginfo[1]>$photoMaxSize)
                {
                    $rs1 = new abeautifulsite\SimpleImage ($dest);

                    try {
                        $thumb = JPATH_ROOT . '/'. $pathOg .'/rz/' . $filename_new;
                        $rs1->auto_orient()->best_fit ($photoMaxSize, $photoMaxSize)
                                    ->save ($thumb);
                    } catch (Exception $e) {
                        $this->setError ($e->getMessage());
                        return false;
                    }
                }

                // create thumbnail image
                $resizeObj = new abeautifulsite\SimpleImage ($dest);

                try {
                    $thumb = JPATH_ROOT . '/'. $pathOg .'/thumb/' . $filename_new;
                    $resizeObj->auto_orient()->thumbnail (140, 140)
                                ->save ($thumb);
                } catch (Exception $e) {
                    $this->setError ($e->getMessage());
                    return false;
                }

                break;

            case 'zip':
            case 'rar':
                $data ['fileType'] = 'compression';

                try {
                    $bytes = file_get_contents ($file['tmp_name'], FALSE, NULL, 0, 7);

                    if ($type == 'rar' && bin2hex($bytes) != '526172211a0700') {
                        throw new Exception (JText::_("JCM_RAR_FILE_INCORRECT"));
                    }

                    if ($type == 'zip' && substr($bytes, 0, 2) != 'PK') {
                        throw new Exception (JText::_("JCM_ZIP_FILE_INCORRECT"));
                    }
                } catch (Exception $e) {
                    $this->setError ($e->getMessage());
                    return false;
                }

                // upload file
                $data ['path'] = $pathOg . '/compression/' . $filename_new;
                $dest = JPATH_ROOT . '/' . $data['path'];

                if(!JFile::upload($file['tmp_name'], $dest, false, true))
                {
                    $this->setError(JText::_("JCM_UPLOAD_FILE_ERROR"));
                    return false;
                }

                break;

            default:
                // other file
                $data ['fileType'] = 'unknown';

                // upload file
                $data ['path'] = $pathOg . '/other/' . $filename_new;
                $dest = JPATH_ROOT . '/' . $data['path'];

                if(!JFile::upload($file['tmp_name'], $dest, false, true))
                {
                    $this->setError("Upload file error.");
                    return false;
                }

                break;
        }

        // import to database
        $row = $this->getTable('media', 'TableCm');
        $row->bind($data);

        if (! $row->store() )
        {
            $this->setError (JText::_("JCM_UPLOAD_FILE_ERROR"));
            return false;
        }

        // assign permission for item
        $session->set("item_" . $row->id, true, "jcm_media");

        return $row->id;
    }

    public function update()
    {
        $app        = JFactory::getApplication();
        $session    = JFactory::getSession();
        $this->id   = $app->input->getInt('id', 0);

        $config     = JLexCommentHelper::getConfig();
        $permission = false;
        $ignore     = [];

        if ($this->_isOwn() || $config->get("u_edit_any_comment",false)==true)
        {
            $permission = true;
        }

        if(!$permission)
        {
            $this->setError (JText::_("JCM_PERMISSION_DENIED"));
            return false;
        }

        $row = $this->getTable('media', 'TableCm');
        $row->load($this->id);

        if(!$row->id)
        {
            $this->setError (JText::_("JCM_MEDIA_NOT_FOUND"));
            return false;
        }

        $description = array_key_exists("description", $_REQUEST) ? $_REQUEST["description"] : "";
        $name = $app->input->getString("name", "");

        if(preg_match("/^\s*$/", $name))
        {
            $this->setError (JText::_("JCM_NAME_FIELD_NOT_EMPTY"));
            return false;
        }

        $row->bind ( array(
                "name" => $name,
                "description" => htmlspecialchars ($description)
            ));

        if(!$row->store())
        {
            $this->setError (JText::_("JCM_APPEAR_ERROR_WHEN_SAVING_YOUR_DATE_TRY_LATER"));
            return false;
        }

        return true;
    }

    public function download()
    {
        $app        = JFactory::getApplication ();
        $user       = JFactory::getUser ();
        $config     = JLexCommentHelper::getConfig ();

        if ($config->get("u_download_file",false)==false && $this->_isOwn()==false)
        {
            $this->setError (JText::_("JCM_PERMISSION_DENIED"));
            return false;
        }

        // downloader ready
        $query = $this->getDbo()->getQuery (true);
        $query->select ('*')->from ('#__jlexcomment_media')->where ('id=' . $this->id);

        $media = $this->getDbo()->setQuery ($query,0,1)->loadObject();

        if (!$media)
        {
            $this->setError (JText::_("JCM_ITEM_NOT_FOUND"));
            return false;
        }

        // exec file
        $extension  = pathinfo($media->fileName, PATHINFO_EXTENSION);
        $name       = $media->name;

        $path = JPATH_ROOT . '/' . $media->path;

        if(!JFile::exists($path))
        {
            $this->setError (JText::_("JCM_ITEM_NOT_FOUND"));
            return false;
        }

        
        if (ini_get('zlib.output_compression')) {
            ini_set('zlib.output_compression', 'Off');
        }
        switch ( $extension ) {
            case "pdf":
                $type = "application/pdf";
                break;
            case "exe":
                $type = "application/octet-stream";
                break;
            case "zip":
                $type = "application/zip";
                break;
            case "doc":
                $type = "application/msword";
                break;
            case "xls":
                $type = "application/vnd.ms-excel";
                break;
            case "ppt":
                $type = "application/vnd.ms-powerpoint";
                break;
            case "gif":
                $type = "image/gif";
                break;
            case "png":
                $type = "image/png";
                break;
            case "jpeg":
            case "jpg":
                $type = "image/jpg";
                break;
            default:
                $type = "application/force-download";
        }
        header("Pragma: public"); // required
        header("Expires: 0");
        header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
        header("Cache-Control: private", false);
        header("Content-Type: $type");

        // change, added quotes to allow spaces in filenames, by Rajkumar Singh
        header("Content-Disposition: attachment; filename=\"" . $name . "\";");
        header("Content-Transfer-Encoding: binary");
        
        $handle = fopen($path, "rb");
        echo fread($handle, filesize($path));

        $app->close();
    }

    public function remove ()
    {
        // only use for admin
    }

    protected $count_tmp_file = 0;

    public function clean()
    {
        set_time_limit(0);

        // delete all attachments that haven't used.
        $query = $this->_db->getQuery(true);
        $query->select('SQL_CALC_FOUND_ROWS m.id')
              ->from('#__jlexcomment_media m')
              ->leftJoin('#__jlexcomment c ON m.comment_id=c.id')
              ->where('c.id IS NULL');

        $rows   = $this->_db->setQuery($query, 0, 30)->loadObjectList();
        $total = $this->_db->setQuery("SELECT FOUND_ROWS()")->loadResult();

        if($total<1 || !$rows) return $this->count_tmp_file;

        $this->count_tmp_file+=$total;

        foreach($rows as $row)
        {
            CommentHelperAdmin::fileDelete($row->id);
        }

        return $this->clean();
    }
}
