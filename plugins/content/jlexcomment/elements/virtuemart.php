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
class JFormFieldVirtuemart extends JFormField {
	protected $type = 'Virtuemart';
	protected function getInput() {
		// Check VM installed yet?
		$VMXML = JPATH_ADMINISTRATOR . '/components/com_virtuemart/virtuemart.xml';
		if (! file_exists ( $VMXML )) {
			return '<span style="color:red">This element requires component Virtuemart!</span>';
		}
		
		// Check version of VM
		$xml = simplexml_load_file ( $VMXML );
		$version = $xml->version . '';
		
		if (version_compare ( $version, '3', '>=' )) {
			// VM 3.x
			$path2admin = JPATH_ROOT.'/administrator/components/com_virtuemart';

			if(!class_exists('VmConfig'))
				require_once $path2admin.'/helpers/config.php';

			if(!class_exists('ShopFunctions'))
				require_once $path2admin.'/helpers/shopfunctions.php';

			/*if(!class_exists('TableCategories'))
				//require_once $path2admin.'/tables/categories.php';*/
			
			$key = ($this->element ['key_field'] ? $this->element ['key_field'] : 'value');
			$val = ($this->element ['value_field'] ? $this->element ['value_field'] : $this->name);
			
			VmConfig::loadConfig ();
			VmConfig::loadJLang ( 'com_virtuemart' );
			
			$categorylist = ShopFunctions::categoryListTree($this->value);
			
			$output = '<select class="inputbox" multiple="multiple" size="10"  name="' . $this->name . '[]" >';
			$output .= '<option value="0">' . vmText::_ ( 'COM_VIRTUEMART_CATEGORY_FORM_TOP_LEVEL' ) . '</option>';
			$output .= $categorylist;
			$output .= "</select>";
		} else {
			// VM 2.x
			if (! class_exists ( 'VmConfig' ))
				require (JPATH_ADMINISTRATOR . '/components/com_virtuemart/helpers/config.php');
			if (! class_exists ( 'ShopFunctions' ))
				require (JPATH_ADMINISTRATOR . '/components/com_virtuemart/helpers/shopfunctions.php');
			if (! class_exists ( 'TableCategories' ))
				require (JPATH_ADMINISTRATOR . '/components/com_virtuemart/tables/categories.php');
			
			if (! class_exists ( 'VmElements' ))
				require (JPATH_ADMINISTRATOR . '/components/com_virtuemart/elements/vmelements.php');
			
			$key = ($this->element ['key_field'] ? $this->element ['key_field'] : 'value');
			$val = ($this->element ['value_field'] ? $this->element ['value_field'] : $this->name);
			
			$this->value = ! is_array ( $this->value ) ? array () : $this->value;
			
			$categorylist = ShopFunctions::categoryListTreeLoop ( $this->value );

			$output = '<select name="' . $this->name . '[]" class="inputbox" multiple="multiple" size="10">';
			$output .= '<option ' . (in_array ( '0', $this->value ) ? 'selected="selected" ' : '') . ' value="0">Select all</option>';
			$output .= stripcslashes ( $categorylist );
			$output .= '</select>';
		}
		
		return $output;
	}
}