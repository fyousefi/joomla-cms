<?php
/**
 * @package     JLex Comment
 * @version     1.0.0
 * @copyright   Copyright (C) 2013-2016 JLexArt Team (http://www.jlexart.com). All rights reserved.
 * @license     GNU/GPL or later
 * @author      JLexArt (support@jlexart.com)
 */

defined ( '_JEXEC' ) or die ();

class JFormFieldJCM_StickerGroup extends JFormField
{
	/**
	 * The form field type.
	 *
	 * @var    string
	 * @since  1.6
	 */
	public $type = 'JCM_StickerGroup';

	/**
	 * Method to get the user field input markup.
	 *
	 * @return  string  The field input markup.
	 *
	 * @since   1.6
	 */
	protected function getInput()
	{
		$options = array (
                JHTML::_("select.option", "0", "User"),
                JHTML::_("select.option", "-1", "- Select Group -")
            );

		$db = JFactory::getDbo ();
		$query = "SELECT id,name FROM #__jlexcomment_sticker_group";
		$groups = $db->setQuery ($query)->loadObjectList ();

        if (count($groups))
        {
            foreach ($groups as $group)
            {
                $options[] = JHTML::_("select.option", $group->id, $group->name);
            }
        }

        $html = JHTML::_ ( 'select.genericlist', 
                        $options , 
                        $this->name, 
                        ' class="input-medium custom-select" ', 
                        'value', 'text',
                        $this->value);

		return $html;
	}
}
