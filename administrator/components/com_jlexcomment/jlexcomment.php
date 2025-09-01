<?php
/**
 * @package     JLex Comment
 * @version     1.0.0
 * @copyright   Copyright (C) 2013-2016 JLexArt Team (http://www.jlexart.com). All rights reserved.
 * @license     GNU/GPL or later
 * @author      JLexArt (support@jlexart.com)
 */

defined('_JEXEC') or die;
if(function_exists('ini_set'))
    ini_set("pcre.jit", "0"); // PHP 7

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

if (! defined("JCM_SERVER"))
{
	//http://www.jlexart.com/connect/jlexcomment
	define("JCM_SERVER", "https://www.jlexart.com/connect/jlexcomment");
}

jimport("joomla.filesystem.folder");
jimport("joomla.filesystem.file");

require_once dirname (__FILE__) . '/libraries/helper.php';
require_once JPATH_SITE . '/components/com_jlexcomment/libraries/helper.php';

CommentHelperAdmin::lis();

$app = JFactory::getApplication();

$controller = JControllerLegacy::getInstance('JLexComment');
$controller->execute( $app->input->get('task', 'display') );
$controller->redirect();