<?php
/**
 * @version		1.0
 * @package		Content - JLex Comment
 * @copyright	Copyright (C) 2013 joomla-extensions.info. All rights reserved.
 * @license		GNU/GPL or later
 * @author		JLEX team
 */
defined ( '_JEXEC' ) or die ( 'Restricted access' );

jimport ( 'joomla.form.formfield' );

class JFormFieldiCagenda extends JFormField
{
	protected $type = 'iCagenda';

	protected function getInput()
	{
		$db = JFactory::getDBO();
		
		// Check K2 installed yet?
		$filek2 = JPATH_ROOT.'/components/com_icagenda/icagenda.php';
		if (!file_exists($filek2)) {
			return '<span style="color:red">You must install <b>iCagenda</b> component!</span>';
		}
		
		$query = $db->getQuery(true);
		$query->select('id,title')
			  ->from('#__icagenda_category')
			  ->order('title ASC');

		$db->setQuery ( $query );
		$result = $db->loadObjectList ();
		
		$mitems = array(
				JHtml::_('select.option',0,JText::_('JALL')),
			);

		if($result)
		{
			foreach($result as $item)
			{
				$mitems[] = JHtml::_('select.option', $item->id, $item->title);
			}
		}

		$fieldName 	= $this->name.'[]'; // allow select multiple categories
		$output 	= JHtml::_('select.genericlist', $mitems, $fieldName, 'class="inputbox" multiple="multiple" size="10"', 'value', 'text', $this->value);
		return $output;
	}
}