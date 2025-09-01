<?php
/**
 * @package     JLex Comment
 * @version     1.0.0
 * @copyright   Copyright (C) 2013-2016 JLexArt Team (http://www.jlexart.com). All rights reserved.
 * @license     GNU/GPL or later
 * @author      JLexArt (support@jlexart.com)
 */

defined ( '_JEXEC' ) or die ();

// determine Joomla! version
if (! defined("JE_JVERSION")) 
{
    if (version_compare(JVERSION, "4.0.0-alpha", ">=")) {
		define("JE_JVERSION", "J4");
	} elseif (version_compare(JVERSION, "3.0.0", ">=")) {
        define("JE_JVERSION", "J3");
    } else {
        define("JE_JVERSION", "J25");
    }
}

jimport('joomla.filesystem.file');
jimport('joomla.filesystem.folder');

JTable::addIncludePath ( JPATH_ADMINISTRATOR . "/components/com_jlexcomment/tables" );
require_once JPATH_ADMINISTRATOR . "/components/com_jlexcomment/libraries/helper.php";
require_once dirname (__FILE__) . "/libraries/helper.php";

// import language pack in back-end
$language = JFactory::getLanguage ();
$language->load ("com_jlexcomment", JPATH_ADMINISTRATOR, null, true);