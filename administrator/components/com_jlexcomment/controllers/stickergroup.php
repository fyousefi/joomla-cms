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

class JLexCommentControllerStickerGroup extends JControllerLegacy
{
	public function add ()
	{
		$url = JUri::base (true) . '/index.php?option=com_jlexcomment&view=stickergroup&layout=form';
		$this->setRedirect ($url);
	}

	public function cancel ()
	{
		$url = JUri::base (true) . '/index.php?option=com_jlexcomment&view=stickergroup';
		$this->setRedirect ($url);
	}

	public function save ()
	{
		$model = $this->getModel ('stickergroup');
		$return = $model->save ();

		if (! $return)
		{
			$url = JUri::base (true) . '/index.php?option=com_jlexcomment&view=stickergroup&layout=form';
			$this->setRedirect ($url, $model->getError(), "error");
			return false;
		}

		$url = JUri::base (true) . '/index.php?option=com_jlexcomment&view=stickergroup';
		$this->setRedirect ($url, JText::_("JCM_ROWS_SAVED"));
	}

	public function publish ()
	{
		$model = $this->getModel ('stickergroup');
		$model->set ('jcm_state', 1);

		$model->state ();

		$url = JUri::base (true) . '/index.php?option=com_jlexcomment&view=stickergroup';
		$this->setRedirect ($url, JText::_("JCM_ROWS_PUBLISHED"));
	}

	public function unpublish ()
	{
		$model = $this->getModel ('stickergroup');
		$model->set ('jcm_state', 0);

		$model->state ();

		$url = JUri::base (true) . '/index.php?option=com_jlexcomment&view=stickergroup';
		$this->setRedirect ($url, JText::_("JCM_ROWS_UNPUBLISHED"));
	}

	public function delete ()
	{
		$model = $this->getModel ('stickergroup');
		$return = $model->remove ();

		$msg = JText::sprintf("JCM_ROWS_DELETED_COUNT", $return['success'] . '/' .$return['total'] );

		$url = JUri::base (true) . '/index.php?option=com_jlexcomment&view=stickergroup';
		$this->setRedirect ($url, $msg);
	}
}
