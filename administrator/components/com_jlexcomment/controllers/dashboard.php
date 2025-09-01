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

class JLexCommentControllerDashboard extends JControllerLegacy
{
	public function statistics ()
	{
		$app = JFactory::getApplication ();
		$period = $app->input->getString ('p', 7);

		$model = $this->getModel ('dashboard');
		$model->set ('period', $period);
		$return = $model->statistics ();

		JLexCommentHelper::mix2json ($return);
	}

	public function others ()
	{
		$app = JFactory::getApplication ();
		$period = $app->input->getInt ('p', 7);

		$model = $this->getModel ('dashboard');
		$model->set ('period', $period);

		$return = $model->others ();

		JLexCommentHelper::mix2json ($return);
	}
}
