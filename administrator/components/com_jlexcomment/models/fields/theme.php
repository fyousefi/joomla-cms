<?php
/**
 * @package     JLex Comment
 * @version     1.0.0
 * @copyright   Copyright (C) 2013-2016 JLexArt Team (http://www.jlexart.com). All rights reserved.
 * @license     GNU/GPL or later
 * @author      JLexArt (support@jlexart.com)
 */

defined ( '_JEXEC' ) or die ();

class JFormFieldJCM_Theme extends JFormField
{
	/**
	 * The form field type.
	 *
	 * @var    string
	 * @since  1.6
	 */
	public $type = 'JCM_Theme';

	/**
	 * Method to get the user field input markup.
	 *
	 * @return  string  The field input markup.
	 *
	 * @since   1.6
	 */
	protected function getInput()
	{
		jimport('joomla.filesystem.folder');
		$options = [];

		$theme_path = JPATH_SITE . '/components/com_jlexcomment/themes';

		if(!JFolder::exists($theme_path))
		{
			return '<span style="color:red">No Theme Found!</span>';
		}

		$folders = JFolder::folders($theme_path);
		$theme = [];
		if(count($folders)>0)
		{
			foreach($folders as $f)
			{
				$options[] = JHtml::_("select.option", $f, ucfirst($f));
			}
		} else {
			return '<span style="color:red">No Theme Found!</span>';
		}

        $html = JHtml::_('select.genericlist', 
                        $options , 
                        $this->name, 
                        ' class="input-medium custom-select" ', 
                        'value', 'text',
                        $this->value);

		return $html;
	}
}
