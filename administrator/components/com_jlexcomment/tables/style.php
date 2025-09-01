<?php
/**
 * @package     JLex Comment
 * @version     1.0.0
 * @copyright   Copyright (C) 2013-2016 JLexArt Team (http://www.jlexart.com). All rights reserved.
 * @license     GNU/GPL or later
 * @author      JLexArt (support@jlexart.com)
 */

defined ( '_JEXEC' ) or die ();

class TableCmStyle extends JTable
{

    var $id         = null;

    var $caption    = null;

    var $css        = null;

    var $maxlength  = null;

    var $published  = null;

    public function __construct (&$db)
    {
        parent::__construct('#__jlexcomment_style', 'id', $db);
    }

    public function check()
    {
        if(preg_match('/^\s*$/', $this->caption))
        {
            $this->setError('JCM_CAPTION_FIELD_NOT_EMPTY');
            return false;
        }

        if(preg_match('/^\s*$/', $this->css))
        {
            $this->setError('JCM_CSS_NOT_EMPTY');
            return false;
        }

        $this->maxlength = intval($this->maxlength);
        if($this->maxlength<0) $this->maxlength = 0;

        return true;
    }
}