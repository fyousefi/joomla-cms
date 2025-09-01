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

class JLexCommentControllerIntegration extends JControllerLegacy
{
	public function install ()
	{
		$model 	= $this->getModel ('integration');
		$return = $model->install ();

		$url = JUri::base (true) . "/index.php?option=com_jlexcomment&view=integration";

		if ($return==false)
		{
			$this->setRedirect ($url, $model->getError(), "error" );
		} else {
			$this->setRedirect ($url, JText::_("JCM_THIS_APP_INSTALLED"));
		}
	}
}
