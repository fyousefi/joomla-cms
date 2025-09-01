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

class JLexCommentControllerImport extends JControllerLegacy
{
	public function upload ()
	{
		$model = $this->getModel ("import");
		$return = $model->upload ();

		if ( $return==false )
		{
			$url = JUri::base (true) . "/index.php?option=com_jlexcomment&view=import";
			$this->setRedirect ($url, $model->getError(), "error");
		} else {
			$url = JUri::base (true) . "/index.php?option=com_jlexcomment&view=import&layout=form&id=";
				$url.= $return;
				
			$this->setRedirect ($url);
		}
	}

	public function migrator ()
	{
		$app = JFactory::getApplication();
		$done = $app->input->getBool("done", false);
		if ($done)
		{
			$total = $app->input->getInt("total",0);
			$url = JUri::base (true) . "/index.php?option=com_jlexcomment&view=import";
			$this->setRedirect ( $url, JText::sprintf("JCM_ROW_IMPORTED", $total));
			return;
		}

		$model = $this->getModel ("import");
		$return = $model->migrator ();

		JLexCommentHelper::mix2json ( $return );
	}

	public function progress ()
	{
		$model = $this->getModel ("import");
		$return = $model->progress ();

		$url = JUri::base (true) . "/index.php?option=com_jlexcomment&view=import";

		$this->setRedirect ( $url, JText::sprintf("JCM_ROW_IMPORTED", $return));
	}
}
