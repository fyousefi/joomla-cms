<?php
/**
 * @package     JLex Comment
 * @version     1.0.0
 * @copyright   Copyright (C) 2013-2016 JLexArt Team (http://www.jlexart.com). All rights reserved.
 * @license     GNU/GPL or later
 * @author      JLexArt (support@jlexart.com)
 */

defined ( '_JEXEC' ) or die ();

class TableCmNotification extends JTable
{

    var $id             = null;

    var $obj_id         = null;

    var $comment_id     = null;

    var $action_type    = null;

    var $user_remind    = null;

    var $guest_name     = null;

    var $created_by     = null;

    var $created_time   = null;

    var $unread = null;

    public function __construct(&$db)
    {
        parent::__construct('#__jlexcomment_notification', 'id', $db);
    }
}