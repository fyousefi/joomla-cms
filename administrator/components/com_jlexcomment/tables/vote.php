<?php
/**
 * @package     JLex Comment
 * @version     1.0.0
 * @copyright   Copyright (C) 2013-2016 JLexArt Team (http://www.jlexart.com). All rights reserved.
 * @license     GNU/GPL or later
 * @author      JLexArt (support@jlexart.com)
 */

defined ( '_JEXEC' ) or die ();

class TableCmVote extends JTable
{

    var $id = '';

    var $comment_id = '';

    var $point = '';

    var $created_by = '';

    var $created_time = '';

    var $ip_address = '';

    var $change_times = '';

    public function __construct(&$db)
    {
        parent::__construct('#__jlexcomment_vote', 'id', $db);
    }
}