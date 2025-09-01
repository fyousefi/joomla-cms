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
class JFormFieldCategoryK2 extends JFormField {
	protected $type = 'CategoryK2';
	protected function getInput() {
		$db = JFactory::getDBO ();
		
		// Check K2 installed yet?
		$filek2 = JPATH_ROOT.'/components/com_k2/k2.php';
		if (!file_exists($filek2)) {
			return '<span style="color:red">This element requires component K2!</span>';
		}
		
		$query = 'SELECT name,id,parent FROM #__k2_categories WHERE trash = 0 ORDER BY parent, ordering';
		$db->setQuery ( $query );
		$mitems = $db->loadObjectList ();
		$children = array ();
		if ($mitems) {
			foreach ( $mitems as $v ) {
				if (K2_JVERSION != '15') {
					$v->title = $v->name;
					$v->parent_id = $v->parent;
				}
				$pt = $v->parent;
				$list = @$children [$pt] ? $children [$pt] : array ();
				array_push ( $list, $v );
				$children [$pt] = $list;
			}
		}
		$list = JHTML::_ ( 'menu.treerecurse', 0, '', array (), $children, 9999, 0, 0 );
		$mitems = array ();
		$mitems [] = JHTML::_ ( 'select.option', 0, 'All' );
		
		foreach ( $list as $item ) {
			$item->treename = JString::str_ireplace ( '&#160;', '- ', $item->treename );
			$mitems [] = JHTML::_ ( 'select.option', $item->id, '   ' . $item->treename );
		}
		$fieldName = $this->name . '[]';
		$output = JHTML::_ ( 'select.genericlist', $mitems, $fieldName, 'class="inputbox" multiple="multiple" size="10"', 'value', 'text', $this->value );
		return $output;
	}
}