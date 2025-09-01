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

class JLexCommentControllerReport extends JControllerLegacy
{
	public function post()
	{
		$model 	= $this->getModel ('report');

		$result = $model->report ();

		if (! $result)
		{
			$response = array(
				'status' => 400,
				'error' => $model->getError()
			);
		} else {
			$response = array(
				'status' => 200
			);
		}

		JLexCommentHelper::mix2json ($response);
	}

	public function ignore() 
	{
		$app   	= JFactory::getApplication ();
		$id		= $app->input->getInt ('id', 0);

		$model = $this->getModel ('report');
		$model->set ('id', $id);

		$result = $model->remove ();

		if (! $result)
		{
			$response = array(
				'status' => 400,
				'error' => $model->getError()
			);
		} else {
			$response = array(
				'status' => 200
			);
		}

		JLexCommentHelper::mix2json ($response);
	}
}
