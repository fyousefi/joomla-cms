<?php
/**
 * @package     JLex Comment
 * @version     1.0.0
 * @copyright   Copyright (C) 2013-2016 JLexArt Team (http://www.jlexart.com). All rights reserved.
 * @license     GNU/GPL or later
 * @author      JLexArt (support@jlexart.com)
 */

defined ( '_JEXEC' ) or die ();

class TableCmComment extends JTable
{
    var $id             = null;

    var $obj_id         = null;

    var $comment        = null;

    var $guest_name     = null;

    var $guest_email    = null;

    var $parent_id      = null;

    var $root_parent_id = null;

    var $child_count    = null;

    var $child_count_active = null;

    var $up_point       = null;

    var $down_point     = null;

    var $report_count   = null;

    var $created_by     = null;

    var $created_time   = null;

    var $modified_by    = null;

    var $modified_time  = null;

    var $published      = null;

    var $featured       = null;

    var $sent           = null;

    var $language       = '*';

    var $ip_address     = null;

    var $sticker_id     = null;

    var $params         = null;

    var $reaction_count = null;

    var $reaction_data  = null;

    var $style_id       = null;

    var $giphy_id       = null;

    public function __construct (&$db)
    {
        parent::__construct('#__jlexcomment', 'id', $db);
    }

    public function check ()
    {
        $app    = JFactory::getApplication ();
        $config = JLexCommentHelper::getConfig();
        $user   = JFactory::getUser ();
        $now    = JFactory::getDate()->toSql();

        if(preg_match('/^\s*$/', $this->comment) && !$this->sticker_id)
        {
            $this->setError(jtext::_("JCM_COMMENT_NOT_EMPTY"));
            return false;
        }

        if(empty($this->obj_id))
        {
            $this->setError(JText::_("JCM_ENTRY_NOT_FOUND"));
            return false;
        }

        // check name & email if user is guest
        if(!$this->created_by)
        {
            if(preg_match('/^\s*$/', $this->guest_name))
            {
                $this->setError(jtext::_("JCM_YOU_MUST_FILL_YOUR_NAME"));
                return false;
            }

            if($config->get('check_name',1)==1)
            {
                $names = $config->get('denied_names', '');
                $names = explode(",", $names);

                if(count($names))
                {
                    $name_error = false;
                    foreach($names as $name_deny)
                    {
                        $name_deny = trim($name_deny);
                        if(stripos($this->guest_name, $name_deny)!==false)
                        {
                            $name_error = true;
                            break;
                        }
                    }

                    if($name_error)
                    {
                        $this->setError(JText::_("JCM_DENIED_GUEST_NAME_MSG"));
                        return false;
                    }
                }
            }

            switch ($config->get('form_email_field','1'))
            {
                case '1':
                    if(!filter_var($this->guest_email, FILTER_VALIDATE_EMAIL))
                    {
                        $this->setError(JText::_("JCM_EMAIL_INCORRECT") );
                        return false;
                    }
                    break;

                case '2':
                    if(!preg_match('/^\s*$/', $this->guest_email) && !filter_var($this->guest_email,FILTER_VALIDATE_EMAIL))
                    {
                        $this->setError(JText::_("JCM_EMAIL_INCORRECT"));
                        return false;
                    }
                    break;
            }
        }


        // max comment for each entry
        if($config->get('max_post',0)>0 && $app->isClient('site') && !$this->id)
        {
            $subQ = "SELECT COUNT(*) FROM #__jlexcomment WHERE ";
                $subQ .= "obj_id=" . $this->obj_id;
                $subQ .= " AND parent_id=0";
                $subQ .= " AND ip_address=" . parent::getDbo()->quote ($this->ip_address);

            $cm_count = parent::getDbo()->setQuery($subQ)->loadResult();

            if($cm_count>=$config->get ('max_post')*1)
            {
                $this->setError(JText::sprintf("JCM_YOU_POSTED_MAXIMUM_COMMENT_ALLOWED", $config->get ('max_post')) );
                return false;
            }
        }

        // get time of previous comment
        if($config->def ('between_cm_post',30) > 0 && $app->isClient('site') && !$this->id )
        {
            $subQ = "SELECT MAX(created_time) FROM #__jlexcomment WHERE ip_address=";
                $subQ .= parent::getDbo()->quote ($this->ip_address);

            $previous = parent::getDbo()->setQuery ($subQ)->loadResult();

            if ($previous)
            {
                $left = strtotime ($this->created_time) - strtotime ($previous);
                if ( $left < $config->def ('between_cm_post',30) )
                {
                    $second = $config->def ('between_cm_post',30);

                    if ($second < 60)
                    {
                        $left_time = JText::sprintf("JCM_SECONDS_COUNT", $second);
                    } else {
                        $left_time = JText::sprintf("JCM_MIN_COUNT", intval($second/60));
                    }

                    $this->setError (JText::sprintf("JCM_BETWEEN_TWO_TIMES_COMMENT",$left_time));
                    return false;
                }
            }
        }

        // adjust variable
        if(!$app->isClient('site'))
        {
            $this->created_time = empty($this->created_time)?$now:JFactory::getDate($this->created_time)->toSql();

            $this->modified_time = empty($this->modified_time)?null:JFactory::getDate($this->modified_time)->toSql();
        }

        if(!empty($this->guest_name) && strlen($this->guest_name)>30)
        {
            $this->guest_name = substr($this->guest_name, 0, 29);
        }

        return true;
    }

    public function store ($updateNulls = false)
    {
        $return = parent::store ($updateNulls);

        if ($return==true)
        {
            CommentHelperAdmin::update($this->obj_id, "item");
            if ( $this->parent_id> 0)
            {
                CommentHelperAdmin::update($this->parent_id, "comment");
            }
        }

        return $return;
    }

    public function delete ( $pk=null )
    {
        if ($this->id < 1)
        {
            // row not found.
            return false;
        }

        $return = parent::delete ($pk);

        if ($return==true)
        {
            CommentHelperAdmin::update($this->obj_id, "item");
            if ( $this->parent_id> 0)
            {
                CommentHelperAdmin::update($this->parent_id, "comment");
            }

            // delete all voting for this comment
            $subQ = "DELETE FROM #__jlexcomment_vote WHERE comment_id=" . $this->id;
            parent::getDbo()->setQuery($subQ)->execute();

            // delete all report
            $subQ = "DELETE FROM #__jlexcomment_report WHERE comment_id=" . $this->id;
            parent::getDbo()->setQuery($subQ)->execute();

            // delete all attachment
            $subQ = "SELECT id FROM #__jlexcomment_media WHERE comment_id=" . $this->id;
            $cid  = parent::getDbo()->setQuery($subQ)->loadObjectList();
        
            if($cid)
            {
                foreach($cid as $item)
                {
                    CommentHelperAdmin::fileDelete($item->id);
                }
            }
        }

        return $return;
    }

    public function do_publish ($state = 1)
    {
        if ($this->id < 1 || $this->published==$state)
        {
            // row not found or state not changed.
            return false;
        }

        $query = "UPDATE #__jlexcomment SET published=" . $state . " WHERE id=" . $this->id;
        parent::getDbo()->setQuery($query)->execute();

        $rowAffect = parent::getDbo()->getAffectedRows();

        if ($rowAffect==1)
        {
            CommentHelperAdmin::update($this->obj_id, "item");
            if ( $this->parent_id> 0)
            {
                CommentHelperAdmin::update($this->parent_id, "comment");
            }
        }

        return $rowAffect==1 ? true : false;
    }
}