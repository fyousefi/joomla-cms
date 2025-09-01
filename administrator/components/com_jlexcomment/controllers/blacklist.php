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

class JLexCommentControllerBlacklist extends JControllerLegacy
{
	public function save ()
	{
		$app = JFactory::getApplication ();
		$id  = $app->input->getInt ("id",0);

		$model = $this->getModel ("blacklist");
		$return = $model->save ();

		if ( !$return)
		{
			$url = JUri::base (true) . "/index.php?option=com_jlexcomment&view=blacklist&layout=form" . ($id>0 ? "&id=" . $id:"");
			$this->setRedirect ($url, $model->getError(), "error");
		} else {
			$url = JUri::base (true) . "/index.php?option=com_jlexcomment&view=blacklist";
			$this->setRedirect ($url, JText::_("JCM_ITEM_ADDED_TO_BLACKLIST"));
		}
	}

	public function add ()
	{
		$url = JUri::base (true) . "/index.php?option=com_jlexcomment&view=blacklist&layout=form";
		$this->setRedirect ($url);
	}

	public function cancel ()
	{
		$url = JUri::base (true) . "/index.php?option=com_jlexcomment&view=blacklist";
		$this->setRedirect ($url);
	}

	public function delete ()
	{
		$model = $this->getModel ('blacklist');
		$return = $model->remove ();

		$url = JUri::base (true) . '/index.php?option=com_jlexcomment&view=blacklist';
		$this->setRedirect ($url, JText::_("JCM_ROWS_DELETED"));
	}
}
