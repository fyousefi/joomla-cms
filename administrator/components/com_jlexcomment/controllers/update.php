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

class JLexCommentControllerUpdate extends JControllerLegacy
{
	public function check_version ()
	{
		$model = $this->getModel ("update");
		$data = $model->check_version ();

		JLexCommentHelper::mix2json ($data);
	}

	public function install ()
	{
		$model = $this->getModel ("update");
		$return = $model->install ();

		if ($return===false)
		{
			$response = array (
					"status" => 400,
					"error" => $model->getError ()
				);
		} else {
			$response = array ("status"=>200);
		}

		JLexCommentHelper::mix2json ($response);
	}
}
