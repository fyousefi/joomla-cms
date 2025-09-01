<?php
/**
 * @package     JLex Comment
 * @version     1.0.0
 * @copyright   Copyright (C) 2013-2016 JLexArt Team (http://www.jlexart.com). All rights reserved.
 * @license     GNU/GPL or later
 * @author      JLexArt (support@jlexart.com)
 */

defined ( '_JEXEC' ) or die ();

class TableCmUser extends JTable
{
    var $userid         = '';

    var $created        = '';

    var $auth           = '';

    var $auth_id        = '';

    var $auth_url       = '';

    var $auth_picture   = '';

    public function __construct (&$db)
    {
        parent::__construct('#__jlexcomment_users', 'id', $db);
    }
}