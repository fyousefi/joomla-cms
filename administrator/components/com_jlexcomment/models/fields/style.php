<?php
/**
 * @package     JLex Comment
 * @version     1.0.0
 * @copyright   Copyright (C) 2013-2016 JLexArt Team (http://www.jlexart.com). All rights reserved.
 * @license     GNU/GPL or later
 * @author      JLexArt (support@jlexart.com)
 */

defined ( '_JEXEC' ) or die ();

class JFormFieldJCM_Style extends JFormField
{
	/**
	 * The form field type.
	 *
	 * @var    string
	 * @since  1.6
	 */
	public $type = 'JCM_Style';

	/**
	 * Method to get the user field input markup.
	 *
	 * @return  string  The field input markup.
	 *
	 * @since   1.6
	 */
	protected function getInput()
	{
		$options = [
                JHtml::_("select.option", "-1", "- Select Style -")
            ];

		$db = JFactory::getDbo();
		$query = $db->getQuery(true);

		$query->select("id, caption")
			  ->from("#__jlexcomment_style");

		$styles = $db->setQuery($query)->loadObjectList();

        if(!empty($styles))
        {
            foreach($styles as $style)
            {
                $options[] = JHtml::_("select.option", $style->id, $style->caption);
            }
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
