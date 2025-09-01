<?php
/**
 * @package     JLex Comment
 * @version     1.0.0
 * @copyright   Copyright (C) 2013-2016 JLexArt Team (http://www.jlexart.com). All rights reserved.
 * @license     GNU/GPL or later
 * @author      JLexArt (support@jlexart.com)
 */

defined ( '_JEXEC' ) or die ();

class TableCmReplacer extends JTable
{
    var $id             = null;

    var $caption        = null;

    var $regexClause    = null;

    var $replaceClause  = null;

    var $group_cid      = null;

    var $created_by     = null;

    var $created_time   = null;

    var $published      = null;

    public function __construct(&$db)
    {
        parent::__construct('#__jlexcomment_replacer', 'id', $db);
    }

    public function check()
    {
        if(empty($this->created_time))
        {
            $this->created_time = JFactory::getDate()->toSql();
        }

        if($this->created_by<1)
        {
            $this->created_by = JFactory::getUser()->id;
        }

        if(is_array($this->group_cid))
        {
            $this->group_cid = implode(",", $this->group_cid);
        }

        if(preg_match("/^\s*$/", $this->caption) || preg_match("/^\s*$/", $this->regexClause) || preg_match("/^\s*$/", $this->replaceClause))
        {
            $this->setError(JText::_("JCM_ALL_FIELD_REQUIRED_MUST_FILL"));
            return false;
        }

        return true;
    }
}