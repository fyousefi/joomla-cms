<?php
/**
 * @package     JLex Comment
 * @version     1.0.0
 * @copyright   Copyright (C) 2013-2016 JLexArt Team (http://www.jlexart.com). All rights reserved.
 * @license     GNU/GPL or later
 * @author      JLexArt (support@jlexart.com)
 */

defined ( '_JEXEC' ) or die ();

class JFormFieldJCodeditor extends JFormField
{
	/**
	 * The form field type.
	 *
	 * @var    string
	 * @since  1.6
	 */
	public $type = 'JCodeditor';

	/**
	 * Method to get the user field input markup.
	 *
	 * @return  string  The field input markup.
	 *
	 * @since   1.6
	 */
	protected function getInput()
	{
		$doc = JFactory::getDocument();
		$asset_path = JUri::base(true).'/components/com_jlexcomment/assets';

		$doc->addScript($asset_path.'/ace/jquery.ace.js');
		$doc->addScript($asset_path.'/sync.js');

		$vars = $this->element['vars'];
		
		if(!empty($vars))
		{
			$vars = preg_split("/[\s,]+/", $vars);
			$vars = '$'.implode(', $', $vars);
		} else {
			$vars = '$object_id';
		}

		$html = '<p>'.jtext::_('JCM_SYNC_GLOBAL_VAR').': <i>'.$vars.'</i></p>';

		if(empty($this->value)) $this->value="<?php\n";
		$html.= '<textarea id="'.$this->id.'" class="aceditor" name="'.$this->name.'">'.$this->value.'</textarea>';
		$html.= '<div></div>';

		return $html;
	}
}
