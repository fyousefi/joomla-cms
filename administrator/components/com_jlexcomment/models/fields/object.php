<?php
/**
 * @package     JLex Comment
 * @version     1.0.0
 * @copyright   Copyright (C) 2013-2016 JLexArt Team (http://www.jlexart.com). All rights reserved.
 * @license     GNU/GPL or later
 * @author      JLexArt (support@jlexart.com)
 */

defined ( '_JEXEC' ) or die ();

class JFormFieldModal_Object extends JFormField
{
	/**
	 * The form field type.
	 *
	 * @var		string
	 * @since   1.6
	 */
	protected $type = 'Modal_Object';

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
		$script[] = '	function jSelectJCMObject_' . $this->id . '(id, title) {';
		$script[] = '		document.getElementById("' . $this->id . '_id").value = id;';
		$script[] = '		document.getElementById("' . $this->id . '_name").value = title;';

		$script[] = '		jQuery("#' . $this->id . '_clear").removeClass("hidden");';

		$script[] = '		jQuery("#modalJCMObject' . $this->id . '").modal("hide");';

		if ($this->required)
		{
			$script[] = '		document.formvalidator.validate(document.getElementById("' . $this->id . '_id"));';
			$script[] = '		document.formvalidator.validate(document.getElementById("' . $this->id . '_name"));';
		}

		$script[] = '	}';

		// Clear button script
		static $scriptClear;

		if (!$scriptClear)
		{
			$scriptClear = true;

			$script[] = '	function jClearJCMObject(id) {';
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
		$config = JLexCommentHelper::getConfig();
		$html 	= array();
		$link 	= 'index.php?option=com_jlexcomment&amp;view=items&amp;layout=modal&amp;tmpl=component&amp;function=jSelectJCMObject_' . $this->id;

		if ((int) $this->value > 0)
		{
			$db    = JFactory::getDbo();
			$query = $db->getQuery(true)
						->select('title,com_name,com_key')
						->from($db->quoteName('#__jlexcomment_obj'))
						->where($db->quoteName('id') . ' = ' . (int) $this->value);
			
			$row   = $db->setQuery($query)->loadObject();

			if (!$row)
			{
				$title = 'Unknow item (id='. $this->value .')';
			} else {
				$title = $row->title;
				if ($config->def("cm_link",0)==1)
				{
					$up 	= CommentHelperAdmin::getObjDetail($row->com_name, $row->com_key, $row->title);
					$title 	= $up->title;
				}
			}
		}

		if (empty($title))
		{
			$title = "Select object";
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
		$html[] = '<a href="#modalJCMObject' . $this->id . '" class="btn hasTooltip btn-primary" role="button" data-bs-toggle="modal" data-toggle="modal" title="'
			. JHtml::tooltipText("Select object") . '">'
			. '<span class="icon-file"></span> '
			. JText::_('JSELECT') . '</a>';


		// Clear article button
		$html[] = '<button id="' . $this->id . '_clear" class="btn' . ($value ? '' : ' hidden') . ' btn-danger" onclick="return jClearJCMObject(\'' .
			$this->id . '\')"><span class="icon-remove"></span>' . JText::_('JCLEAR') . '</button>';

		$html[] = '</span>';

		// The class='required' for client side validation
		$class = '';

		if ($this->required)
		{
			$class = ' class="required modal-value"';
		}

		$html[] = '<input type="hidden" id="' . $this->id . '_id"' . $class . ' name="' . $this->name . '" value="' . $value . '" />';


		$html[] = JHtml::_(
			'bootstrap.renderModal',
			'modalJCMObject' . $this->id,
			array(
				'url' => $link,
				'title' => "Select object",
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
