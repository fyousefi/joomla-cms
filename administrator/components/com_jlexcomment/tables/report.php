<?php
/**
 * @package     JLex Comment
 * @version     1.0.0
 * @copyright   Copyright (C) 2013-2016 JLexArt Team (http://www.jlexart.com). All rights reserved.
 * @license     GNU/GPL or later
 * @author      JLexArt (support@jlexart.com)
 */

defined ( '_JEXEC' ) or die ();

class TableCmReport extends JTable
{

    var $id = '';

    var $comment_id     = '';

    var $reason_code    = '';

    var $reason_msg     = '';

    var $created_by     = '';

    var $created_time   = '';

    var $ip_address     = '';

    var $guest_name     = '';

    var $guest_email    = '';

    public function __construct(&$db)
    {
        parent::__construct('#__jlexcomment_report', 'id', $db);
    }

    public function check ()
    {
        if ( $this->created_by==0 )
        {
            if ( preg_match('/^\s*$/', $this->guest_name) )
            {
                $this->setError ( JText::_("JCM_YOU_MUST_FILL_YOUR_NAME") );
                return false;
            }

            if (! filter_var ( $this->guest_email, FILTER_VALIDATE_EMAIL )) {
                $this->setError ( JText::_("JCM_EMAIL_INCORRECT") );
                return false;
            }
        }
        
        return true;
    }

    public function store($updateNulls = false)
    {
        $isNew = $this->id > 0 ? false : true;

        if (parent::store($updateNulls))
        {
            if ($isNew)
            {
                $query = "UPDATE #__jlexcomment SET report_count=report_count+1 WHERE id=".$this->comment_id;
                parent::getDbo()->setQuery($query)->execute();
            }

            return true;
        }

        return false;
    }

    public function delete($pk=null)
    {
        if ($this->id < 1)
        {
            return false;
        }

        if ( parent::delete($pk))
        {
            $query = "UPDATE #__jlexcomment SET report_count=report_count-1 WHERE id=".$this->comment_id;
            parent::getDbo()->setQuery($query)->execute();

            return true;
        }

        return false;
    }
}