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

class JLexCommentControllerMailq extends JControllerLegacy
{
	public function markpending ()
	{
		$url = JUri::base (true) . '/index.php?option=com_jlexcomment&view=mailq';

		$model 	= $this->getModel ('mailq');
		$model->set ('status', 0);
		$return = $model->markas ();

		$this->setRedirect ($url, JText::_("JCM_MAILQ_TO_PENDING"));
	}

	public function marksent ()
	{
		$url = JUri::base (true) . '/index.php?option=com_jlexcomment&view=mailq';

		$model 	= $this->getModel ('mailq');
		$model->set ('status', 1);
		$return = $model->markas ();

		$this->setRedirect ($url, JText::_("JCM_MAILQ_TO_SENT"));
	}
}
