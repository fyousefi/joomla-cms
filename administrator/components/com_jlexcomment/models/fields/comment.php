<?php
/**
 * @package     JLex Comment
 * @version     1.0.0
 * @copyright   Copyright (C) 2013-2016 JLexArt Team (http://www.jlexart.com). All rights reserved.
 * @license     GNU/GPL or later
 * @author      JLexArt (support@jlexart.com)
 */

defined ( '_JEXEC' ) or die ();

class JFormFieldModal_Comment extends JFormField
{
	/**
	 * The form field type.
	 *
	 * @var		string
	 * @since   1.6
	 */
	protected $type = 'Modal_Comment';

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
		$script[] = '	function jSelectJCMComment_' . $this->id . '(id, title) {';
		$script[] = '		document.getElementById("' . $this->id . '_id").value = id;';
		$script[] = '		document.getElementById("' . $this->id . '_name").value = title;';

		$script[] = '		jQuery("#' . $this->id . '_clear").removeClass("hidden");';

		$script[] = '		jQuery("#modalJCMComment' . $this->id . '").modal("hide");';


		$script[] = '	}';

		// Clear button script
		static $scriptClear;

		if (!$scriptClear)
		{
			$scriptClear = true;

			$script[] = '	function jClearJCMComment(id) {';
			$script[] = '		document.getElementById(id + "_id").value = "";';
			$script[] = '		document.getElementById(id + "_name").value = "' .
				htmlspecialchars( "No parent comment", ENT_COMPAT, 'UTF-8') . '";';
			$script[] = '		jQuery("#"+id + "_clear").addClass("hidden");';
			$script[] = '		if (document.getElementById(id + "_edit")) {';
			$script[] = '			jQuery("#"+id + "_edit").addClass("hidden");';
			$script[] = '		}';
			$script[] = '		return false;';
			$script[] = '	}';
		}

		// Add the script to the document head.
		JFactory::getDocument()->addScriptDeclaration(implode("\n", $script));

		// Setup variables for display.
		$html = array();
		$link = 'index.php?option=com_jlexcomment&amp;view=comments&amp;layout=modal&amp;tmpl=component&amp;function=jSelectJCMComment_' . $this->id;

		if ((int) $this->value > 0)
		{
			$db    = JFactory::getDbo();
			$query = $db->getQuery(true)
				->select($db->quoteName('comment'))
				->from($db->quoteName('#__jlexcomment'))
				->where($db->quoteName('id') . ' = ' . (int) $this->value);
			$db->setQuery($query);

			try
			{
				$title = $db->loadResult();

				if (empty ($title))
				{
					$title = "Sticker";
				} else {
					// markup title
					$title = preg_replace_callback('/\{u-([1-9][0-9]*),(.*?)\}/', function($matches){
	                    return '<span class="jcm-mention-html" data-id="'.$matches[1].'">@'.$matches[2].'</span>';
	                }, $title);

	                $title = substr ($title, 0, 20) . "...";
				}
			}
			catch (RuntimeException $e)
			{
				JError::raiseWarning(500, $e->getMessage());
			}
		}

		if (empty($title))
		{
			$title = "No parent comment";
		}
		$title = htmlspecialchars($title, ENT_QUOTES, 'UTF-8');

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
		$html[] = '<span class="input-append input-group">';
		$html[] = '<input type="text" class="input-medium form-control" id="' . $this->id . '_name" value="' . $title . '" disabled="disabled" size="35" />';
		$html[] = '<a href="#modalJCMComment' . $this->id . '" class="btn hasTooltip btn-primary" role="button" data-bs-toggle="modal" data-toggle="modal" title="'
			. JHtml::tooltipText("Add parent comment") . '">'
			. '<span class="icon-file"></span> '
			. JText::_('JSELECT') . '</a>';


		// Clear article button
		$html[] = '<button id="' . $this->id . '_clear" class="btn' . ($value ? '' : ' hidden') . ' btn-danger" onclick="return jClearJCMComment(\'' .
			$this->id . '\')"><span class="icon-remove"></span>' . JText::_('JCLEAR') . '</button>';

		$html[] = '</span>';

		$html[] = '<input type="hidden" id="' . $this->id . '_id" name="' . $this->name . '" value="' . $value . '" />';

		$html[] = JHtml::_(
			'bootstrap.renderModal',
			'modalJCMComment' . $this->id,
			array(
				'url' => $link,
				'title' => "Select parent comment",
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
