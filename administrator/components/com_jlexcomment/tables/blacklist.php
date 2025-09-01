<?php
/**
 * @package     JLex Comment
 * @version     1.0.0
 * @copyright   Copyright (C) 2013-2016 JLexArt Team (http://www.jlexart.com). All rights reserved.
 * @license     GNU/GPL or later
 * @author      JLexArt (support@jlexart.com)
 */

defined ( '_JEXEC' ) or die ();

class TableCmBlacklist extends JTable
{
    var $id               = null;

    var $method           = null;

    var $method_value     = null;

    var $reason           = null;

    var $created_by       = null;

    var $created_time     = null;

    public function check ()
    {
        if($this->method==1 && $this->method_value<1)
        {
            $this->setError(JText::_("JCM_USER_NOT_FOUND"));
            return false;
        }

        /**
        if ( $this->method==2 && !filter_var ($this->method_value, FILTER_VALIDATE_EMAIL) )
        {
            $this->setError (JText::_("JCM_EMAIL_INCORRECT"));
            return false;
        }
        **/

        if($this->method==3 && preg_match("/^\s*$/", $this->method_value))
        {
            $this->setError(JText::_("JCM_IP_ADDRESS_NOT_EMPTY"));
            return false;
        }

        if(empty($this->created_time))
        {
            $this->created_time = JFactory::getDate()->toSql();
        }

        if($this->created_by<1)
        {
            $this->created_by = JFactory::getUser()->id;
        }

        return true;
    }

    public function __construct(&$db)
    {
        parent::__construct('#__jlexcomment_blacklist', 'id', $db);
    }
}