<?php
/**
 * @package     JLex Comment
 * @version     1.0.0
 * @copyright   Copyright (C) 2013-2016 JLexArt Team (http://www.jlexart.com). All rights reserved.
 * @license     GNU/GPL or later
 * @author      JLexArt (support@jlexart.com)
 */

defined ( '_JEXEC' ) or die ();

class TableCmSubscribe extends JTable
{
    var $id                 = null;

    var $obj_id             = null;

    var $name               = null;

    var $email              = null;

    var $email_hash         = null;

    var $email_confirmed    = null;

    var $created_by         = null;

    var $created_time       = null;

    var $point              = null;

    var $published          = null;

    public function __construct(&$db)
    {
        parent::__construct('#__jlexcomment_subscribe', 'id', $db);
    }

    public function getId()
    {
        return $this->id;
    }

    public function check()
    {
        $app         = JFactory::getApplication();
        $this->name  = trim($this->name);
        $this->email = strtolower(trim($this->email));

        if(is_null($this->published)) $this->published=1;

        if(preg_match('/^\s*$/', $this->name))
        {
            $this->setError(JText::_("JCM_YOU_MUST_FILL_YOUR_NAME"));
            return false;
        }

        if(filter_var($this->email, FILTER_VALIDATE_EMAIL)=== false)
        {
            $this->setError(JText::_("JCM_EMAIL_INCORRECT"));
            return false;
        }

        if(empty($this->created_time))
            $this->created_time = JFactory::getDate()->toSql();

        // check if email address is joined
        $query = $this->_db->getQuery(true);
        $query->select('id')
              ->from('#__jlexcomment_subscribe');

        if($this->created_by>0)
        {
            $query->where("(created_by={$this->created_by} OR email=" . $this->_db->quote($this->email). ")");
            if(is_null($this->email_confirmed))
            {
                $this->email_confirmed = 1;
            }
        } else {
            $query->where("email=" . $this->_db->quote($this->email));
        }

        $query->where('obj_id='.intval($this->obj_id));

        if($this->id>0)
        {
            $query->where('id!='.$this->id);
        }

        $result = $this->_db->setQuery($query,0,1)->loadResult();

        if($result)
        {
            $this->id = (int) $result;
            $this->setError(JText::_("JCM_SUBSCRIBE_DUPLICATE_EMAIL"));
            return false;
        }

        if($this->id<1)
        {
            // create hash code: Using when unsubscribe from email
            $this->email_hash = substr(md5($this->name.$this->created_time.$this->obj_id), 0, 16) . substr(md5($this->email), 16, 16);
        }

        // check email is verify
        if(is_null($this->email_confirmed)) 
        {
            $query = "SELECT COUNT(*) FROM #__jlexcomment_subscribe WHERE email_confirmed=1 AND email=" . parent::getDbo()->quote($this->email);
            $result = parent::getDbo()->setQuery ($query,0,1)->loadResult ();

            if($result && $app->isClient('administrator') )
            {
                $this->email_confirmed = 1;
            } else {
                $this->email_confirmed = 0;
            }
        }

        return true;
    }
}