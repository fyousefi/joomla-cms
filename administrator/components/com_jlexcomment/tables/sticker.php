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

class TableCmSticker extends JTable
{
    var $id             = null;

    var $caption        = null;

    var $path2file      = null;

    var $group_id       = null;

    var $created_by     = null;

    var $created_time   = null;

    var $published      = null;

    public function __construct(&$db)
    {
        parent::__construct('#__jlexcomment_sticker', 'id', $db);
    }

    public function check ()
    {
        if(preg_match("/^\s*$/", $this->caption))
        {
            $this->setError(JText::_("JCM_CAPTION_FIELD_NOT_EMPTY"));
            return false;
        }

        if($this->group_id==-1)
        {
            $this->setError(JText::_("JCM_YOU_MUST_SELECT_STICKER_GROUP"));
            return false;
        }

        if(empty($this->path2file))
        {
            $this->setError(JText::_("JCM_SELECT_FILE_TO_UPLOAD"));
            return false;
        }

        return true;
    }

    public function delete($pk=null)
    {
        if ($this->id < 1)
        {
            return false;
        }

        if (!parent::delete($pk))
        {
            return false;
        }

        $file = JPATH_ROOT . "/" . $this->path2file;

        if ( JFile::exists ($file))
        {
            JFile::delete ($file);
        }

        return true;
    }
}