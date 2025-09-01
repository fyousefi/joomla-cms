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

require_once dirname (__FILE__) . '/bootstrap.php';

$app = JFactory::getApplication();

$controller = JControllerLegacy::getInstance('JLexComment');
$controller->execute( $app->input->get('task', 'display') );
$controller->redirect();