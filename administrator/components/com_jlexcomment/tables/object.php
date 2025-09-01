<?php
/**
 * @package     JLex Comment
 * @version     1.0.0
 * @copyright   Copyright (C) 2013-2016 JLexArt Team (http://www.jlexart.com). All rights reserved.
 * @license     GNU/GPL or later
 * @author      JLexArt (support@jlexart.com)
 */

defined ( '_JEXEC' ) or die ();

class TableCmObject extends JTable
{
    var $id                 = null;

    var $title              = null;

    var $com_name           = null;

    var $com_key            = null;

    var $com_id             = null;

    var $created_by         = null;

    var $created_time       = null;

    var $cm_count           = null;

    var $cm_count_active    = null;

    var $cm_i_count         = null;

    var $cm_i_count_active  = null;

    var $url                = null;

    var $published          = null;

    var $params             = null;

    public function __construct(&$db)
    {
        parent::__construct('#__jlexcomment_obj', 'id', $db);
    }

    public function check()
    {
        if (preg_match("/^\s*$/", $this->title))
        {
            $this->setError(JText::_("JCM_NAME_FIELD_NOT_EMPTY"));
            return false;
        }

        if (! preg_match("/^[A-z0-9\_]{2,}$/", $this->com_name))
        {
            $this->setError(JText::_("JCM_TYPE_OF_ENTRY_NOT_EMPTY"));
            return false;
        }

        if (preg_match("/^\s*$/", $this->com_key))
        {
            $this->setError(JText::_("JCM_UNIQUE_KEY_ENTRY_NOT_EMPTY"));
            return false;
        }

        if (is_numeric($this->com_key) && is_int($this->com_key*1) && $this->com_key>0)
        {
            $this->com_id = $this->com_key*1;
        } else {
            $this->com_id = 0;
        }

        // check object is existing
        $subQuery = "SELECT id FROM #__jlexcomment_obj WHERE ";
            $subQuery.= "com_name=" . $this->getDbo()->quote($this->com_name);
            $subQuery.= " AND com_key=" . $this->getDbo()->quote($this->com_key);

        $exist = parent::getDbo()->setQuery ($subQuery,0,1)->loadResult ();
        if ($exist>0 && $this->id!=$exist)
        {
            $this->setError ( JText::sprintf("JCM_THIS_ENTRY_ALREADY_EXISTS", $this->com_name, $this->com_key));
            return false;
        }


        if (preg_match("/^\s*$/", $this->url) || preg_match("/^http(s)?:\/\//i", $this->url))
        {
            $this->setError(JText::_("JCM_URL_FIELD_BLANK_OR_INCORRECT"));
            return false;
        }

        $this->com_id = is_numeric($this->com_key) ? $this->com_key*1 : 0;

        $this->params = json_encode($this->params);

        return true;
    }

    public function delete($pk=null)
    {
        if($this->id<1 || !parent::delete($pk)) return false;

        // delete all voting for this comment
        $query = "DELETE FROM #__jlexcomment WHERE obj_id=".$this->id;
        parent::getDbo()->setQuery($query)->execute();

        return true;
    }
}