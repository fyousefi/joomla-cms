<?php
/**
 * @version		1.0.0
 * @package		Content - JLex Comment
 * @copyright	Copyright (C) 2013-2016 JLexArt Team. All rights reserved.
 * @license		GNU/GPL or later
 * @author		JLexArt.COM (support@jlexart.com)
 */

defined('_JEXEC') or die;

jimport ( 'joomla.form.formfield' );
class JFormFieldJDownloads extends JFormField {
	protected $type = 'JDownloads';
	protected function getInput() {
		$db = JFactory::getDBO ();
		
		// Check K2 installed yet?
		$filek2 = JPATH_ROOT.'/components/com_jdownloads/jdownloads.php';
		if (!file_exists($filek2)) {
			return '<span style="color:red">This element requires component JDownloads!</span>';
		}
		
		$query = 'SELECT title,id,parent_id FROM #__jdownloads_categories ORDER BY parent_id, ordering';
		$db->setQuery ( $query );
		$mitems = $db->loadObjectList ();
		$children = array ();
		if ($mitems) {
			foreach ( $mitems as $v ) {
				$pt = $v->parent_id;
				$list = @$children [$pt] ? $children [$pt] : array ();
				array_push ( $list, $v );
				$children [$pt] = $list;
			}
		}
		$list = JHTML::_ ( 'menu.treerecurse', 0, '', array (), $children, 9999, 0, 0 );
		$mitems = array ();
		
		foreach ( $list as $item ) {
			$item->treename = JString::str_ireplace ( '&#160;', '- ', $item->treename );
			$mitems [] = JHTML::_ ( 'select.option', $item->id, '   ' . $item->treename );
		}
		$fieldName = $this->name . '[]';
		$output = JHTML::_ ( 'select.genericlist', $mitems, $fieldName, 'class="inputbox" multiple="multiple" size="10"', 'value', 'text', $this->value );
		return $output;
	}
}