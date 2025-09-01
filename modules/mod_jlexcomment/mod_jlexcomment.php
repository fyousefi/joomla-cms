<?php
/**
 * @version		1.0.0
 * @package		JLex Comment
 * @subpackage	Module JLex Comment
 * @copyright	Copyright (C) 2013-2016 JLexArt. All rights reserved.
 * @license		GNU/GPL 2 or later
 * @author		JLexArt
 */
defined ( "_JEXEC" ) or die;

// Check component installed
$app = JFactory::getApplication ();
$loader = JPATH_ROOT . "/components/com_jlexcomment/bootstrap.php";
$model  = JPATH_ROOT . "/components/com_jlexcomment/models/items.php";

if (! file_exists ( $loader ))
{
	$app->enqueueMessage ( 'Please install JLex Comment before use it.', 'error' );
	return false;
}

require_once $loader;
require_once dirname ( __FILE__ ) . "/helper.php";

JHtml::_('jquery.framework');

$doc = JFactory::getDocument ();
$doc->addStyleSheet ( JUri::base ( true ) . '/modules/mod_jlexcomment/assets/style.css' );
$doc->addScript ( JUri::base ( true ) . '/modules/mod_jlexcomment/assets/script.js' );

// Loader paramters of JLex Review
require_once ($loader);
require_once ($model);
$config = JLexCommentHelper::getConfig();
$helper = new ModJLexCommentHelper( $config, $params );

$moduleclass_sfx = htmlspecialchars($params->get('moduleclass_sfx', ''));
include (JModuleHelper::getLayoutPath ( 'mod_jlexcomment', $params->get ( 'layout', 'default' ) ));