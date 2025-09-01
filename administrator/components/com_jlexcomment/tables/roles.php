<?php
/**
 * @package     JLex Comment
 * @version     1.0.0
 * @copyright   Copyright (C) 2013-2016 JLexArt Team (http://www.jlexart.com). All rights reserved.
 * @license     GNU/GPL or later
 * @author      JLexArt (support@jlexart.com)
 */

defined ( '_JEXEC' ) or die ();

class TableCmRoles extends JTable
{
    var $id         = null;

    var $group_id   = null;

    var $title      = null;

    var $colour     = null;

    var $published  = null;

    var $created_time = null;

    var $created_by = null;

    public function check ()
    {
        if($this->group_id<1)
        {
            $this->setError (JText::_("JCM_SELECT_USERGROUP_TO_SAVE"));
            return false;
        }

        if(preg_match("/^\s*$/", $this->title))
        {
            $this->setError (JText::_("JCM_CAPTION_FIELD_NOT_EMPTY"));
            return false;
        }

        if(!preg_match("/^#([a-fA-F0-9]{3}){1,2}$/", $this->colour))
        {
            $this->setError(JText::_("JCM_COLOR_INCORRECT"));
            return false;
        }

        if(empty($this->created_time))
        {
            $this->created_time = JFactory::getDate()->toSql();
        }

        return true;
    }

    public function __construct(&$db)
    {
        parent::__construct('#__jlexcomment_roles', 'id', $db);
    }
}