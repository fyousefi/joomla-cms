<?php
/**
 * @package     JLex Comment
 * @version     1.0.0
 * @copyright   Copyright (C) 2013-2016 JLexArt Team (http://www.jlexart.com). All rights reserved.
 * @license     GNU/GPL or later
 * @author      JLexArt (support@jlexart.com)
 */

defined ( '_JEXEC' ) or die ();

class TableCmStickerGroup extends JTable
{
    var $id             = null;

    var $name           = null;

    var $description    = null;

    var $created_by     = null;

    var $created_time   = null;

    var $published      = null;

    public function __construct(&$db)
    {
        parent::__construct('#__jlexcomment_sticker_group', 'id', $db);
    }

    public function check()
    {
        if(!$this->id)
            $this->created_time = JFactory::getDate()->toSql();
        
        $this->created_by = JFactory::getUser()->id;

        if(preg_match("/^\s*$/", $this->name))
        {
            $this->setError(JText::_("JCM_NAME_FIELD_NOT_EMPTY"));
            return false;
        }

        return true;
    }
}