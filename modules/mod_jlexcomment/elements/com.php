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

class JFormFieldCom extends JFormField
{
	var $type = 'com';

	public function getInput()
	{
		$app = JFactory::getApplication ();
		$loader = JPATH_ROOT . '/components/com_jlexcomment/load.php';

		if (! file_exists ( $loader ))
		{
			$app->enqueueMessage ( 'Please install JLex Comment before use it.', 'error' );
			return false;
		}

		$db = JFactory::getDbo ();
		$query = "SELECT name,element FROM #__extensions WHERE type='component' AND enabled=1 AND element!='com_jlexreview' ORDER BY name ASC";
		$db->setQuery ( $query );
		$exts = $db->loadObjectList ();
		
		$mitems = array ();
		$mitems [] = JHTML::_ ( 'select.option', '0', '   ' . 'Select all' );
		foreach ( $exts as $item ) {
			$mitems [] = JHTML::_ ( 'select.option', preg_replace ( '/^(com)\_/i', '', $item->element ), $item->name );
		}
		
		$fieldName = $this->name . '[]';
		$output = JHTML::_ ( 'select.genericlist', $mitems, $fieldName, 'class="inputbox" multiple="multiple" size="10"', 'value', 'text', $this->value );
		return $output;
	}
}