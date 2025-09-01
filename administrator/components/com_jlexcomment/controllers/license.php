<?php
/**
 * @package     JLex Comment
 * @version     1.0.0
 * @copyright   Copyright (C) 2013-2016 JLexArt Team (http://www.jlexart.com). All rights reserved.
 * @license     GNU/GPL or later
 * @author      JLexArt (support@jlexart.com)
 */

defined ( '_JEXEC' ) or die ();

require_once JPATH_COMPONENT . '/controller.php';

class JLexCommentControllerLicense extends JControllerLegacy
{
	public function activate()
	{
		set_time_limit(0);
		
		$model = $this->getModel ("license");
		$return = $model->activate ();

		if (! $return)
		{
			$this->setRedirect (JRoute::_("index.php?option=com_jlexcomment&view=license", false), $model->getError(), "error");
		} else {
			$this->setRedirect (JRoute::_("index.php?option=com_jlexcomment&view=dashboard", false), "Congratulation! Now you can use this extension lifetime.");
		}
	}
}
