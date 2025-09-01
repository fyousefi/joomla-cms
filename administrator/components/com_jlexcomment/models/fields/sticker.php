<?php
/**
 * @package     JLex Comment
 * @version     1.0.0
 * @copyright   Copyright (C) 2013-2016 JLexArt Team (http://www.jlexart.com). All rights reserved.
 * @license     GNU/GPL or later
 * @author      JLexArt (support@jlexart.com)
 */

defined ( '_JEXEC' ) or die ();

class JFormFieldModal_Sticker extends JFormField
{
	/**
	 * The form field type.
	 *
	 * @var		string
	 * @since   1.6
	 */
	protected $type = 'Modal_Sticker';

	/**
	 * Method to get the field input markup.
	 *
	 * @return  string	The field input markup.
	 *
	 * @since   1.6
	 */
	protected function getInput()
	{
		// Build the script.
		$script = array();

		// Select button script
		$script[] = '	function jSelectJCMSticker_' . $this->id . '(id, image) {';
		$script[] = '		document.getElementById("' . $this->id . '_id").value = id;';
		$script[] = '		jQuery("#'. $this->id .'_preview").empty().append("<img src=\""+image+"\" />"); ';

		$script[] = '		jQuery("#' . $this->id . '_clear").removeClass("hidden");';

		$script[] = '		jQuery("#modalJCMSticker' . $this->id . '").modal("hide");';


		$script[] = '	}';

		// Clear button script
		static $scriptClear;

		if (!$scriptClear)
		{
			$scriptClear = true;

			$script[] = '	function jClearJCMSticker(id) {';
			$script[] = '		document.getElementById(id + "_id").value = "";';
			$script[] = '		jQuery("#"+id + "_preview").empty().append("<span>'.JText::_('JSELECT').'</span>"); ';

			$script[] = '		jQuery("#"+id + "_clear").addClass("hidden");';
			$script[] = '		return false;';
			$script[] = '	}';
		}

		// Add the script to the document head.
		JFactory::getDocument()->addScriptDeclaration(implode("\n", $script));

		// Setup variables for display.
		$html = array();
		$link = 'index.php?option=com_jlexcomment&amp;view=sticker&amp;layout=modal&amp;tmpl=component&amp;function=jSelectJCMSticker_' . $this->id;
		$preview = null;

		if ((int) $this->value > 0)
		{
			$db    = JFactory::getDbo();
			$query = $db->getQuery(true)
				->select('*')
				->from($db->quoteName('#__jlexcomment_sticker'))
				->where($db->quoteName('id') . ' = ' . (int) $this->value);
			$db->setQuery($query);

			$sticker = $db->loadObject ();

			if ($sticker)
			{
				$preview = JUri::root (true) . "/" . $sticker->path2file;
			}
		}

		// The active article id field.
		if (0 == (int) $this->value)
		{
			$value = '';
		}
		else
		{
			$value = (int) $this->value;
		}

		// The current article display field.
		$html[] = '<span class="input-append">';
		$html[] = '<a href="#modalJCMSticker' . $this->id . '" class="btn hasTooltip btn-primary" role="button" data-bs-toggle="modal" data-toggle="modal" title="'
			. JHtml::tooltipText("Change sticker") . '" id="' . $this->id . '_preview">';

		if ($value>0)
		{
			if ($preview)
			{
				$html[] = '<img src="'.$preview.'" />';
			} else {
				$html[] = 'No image available';
			}
		} else {
			$html[] = JText::_('JSELECT');
		}

		$html[] = '</a>';


		// Clear article button
		$html[] = '<button id="' . $this->id . '_clear" class="btn' . ($value ? '' : ' hidden') . '" onclick="return jClearJCMSticker(\'' .
			$this->id . '\')"><span class="icon-remove"></span>' . JText::_('JCLEAR') . '</button>';

		$html[] = '</span>';

		$html[] = '<input type="hidden" id="' . $this->id . '_id" name="' . $this->name . '" value="' . $value . '" />';

		$html[] = JHtml::_(
			'bootstrap.renderModal',
			'modalJCMSticker' . $this->id,
			array(
				'url' => $link,
				'title' => "Add/change sticker.",
				'width' => '800px',
				'height' => '400px',
				'footer' => '<button class="btn" data-dismiss="modal" aria-hidden="true">'
					. JText::_("JLIB_HTML_BEHAVIOR_CLOSE") . '</button>'
			)
		);
		return implode("\n", $html);
	}

	/**
	 * Method to get the field label markup.
	 *
	 * @return  string  The field label markup.
	 *
	 * @since   3.4
	 */
	protected function getLabel()
	{
		return str_replace($this->id, $this->id . '_id', parent::getLabel());
	}
}
