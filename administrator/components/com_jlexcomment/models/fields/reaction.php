<?php
/**
 * @package     JLex Comment
 * @version     1.0.0
 * @copyright   Copyright (C) 2013-2016 JLexArt Team (http://www.jlexart.com). All rights reserved.
 * @license     GNU/GPL or later
 * @author      JLexArt (support@jlexart.com)
 */

defined ( '_JEXEC' ) or die ();

class JFormFieldJCM_Reaction extends JFormField
{
	/**
	 * The form field type.
	 *
	 * @var    string
	 * @since  1.6
	 */
	public $type = 'JCM_Reaction';

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
		$doc->addStyleSheet ( JUri::base(true) . "/components/com_jlexcomment/assets/colpick.css" );
		$doc->addScript ( JUri::base(true) . "/components/com_jlexcomment/assets/colpick.js" );
		$doc->addScript ( JUri::base(true) . "/components/com_jlexcomment/assets/reaction.js" );

		$html = array(
				'<textarea id="jcm-reaction-data" style="display:none" name="'.$this->name.'">' . $this->value . '</textarea>'
			);

		$scripts = array(
				"jQuery(function($){",
				"JCMReaction($,'" . (!empty($this->value)?$this->value:"") . "');",
				"});"
			);

		$doc->addScriptDeclaration( implode(PHP_EOL, $scripts));

		$html[]= '<div id="jcm-reaction-text">';
			$html[] = '<button type="button" class="_add btn btn-small btn-sm btn-success">'.JText::_('JCM_ADD').'</button>';
		$html[]= '</div>';

		$html[]= '<div class="jcm-reaction-layout">';
			$html[] = '<input type="text" data-name="label" value="" placeholder="Love">';
			$html[] = '<input type="text" data-name="icon" value="" placeholder="/image/love.png">';
			$html[] = '<input type="text" data-name="color" value="" placeholder="#ff1e5f">';
			$html[] = '<input type="hidden" data-name="id" value="">';
			$html[] = '<button type="button" class="btn btn-small btn-sm btn-danger _remove" ">X</button>';
		$html[]= '</div>';

		return implode(PHP_EOL, $html);
	}
}
