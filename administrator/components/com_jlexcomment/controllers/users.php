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

class JLexCommentControllerUsers extends JControllerLegacy
{
	public function upload ()
	{
		$url = JUri::base (true) . '/index.php?option=com_jlexcomment&view=users';
		$model 	= $this->getModel ('users');
		$return = $model->upload ();

		if ($return==false)
		{
			$this->setRedirect ($url, $model->getError(), "error" );
		} else {
			$this->setRedirect ($url, JText::_("JCM_THUMB_CHANGED"));
		}
	}

	public function add2bl ()
	{
		$model = $this->getModel ("users");
		$model->add2bl ();

		$url = JUri::base (true) . '/index.php?option=com_jlexcomment&view=users';
		$this->setRedirect ($url, JText::_("JCM_USERS_ADD_BLACKLIST"));
	}

	public function outbl ()
	{
		$model = $this->getModel ("users");
		$model->outbl ();

		$url = JUri::base (true) . '/index.php?option=com_jlexcomment&view=users';
		$this->setRedirect ($url, JText::_("JCM_USERS_ADD_WHITELIST"));
	}

	public function clearthumb ()
	{
		$model = $this->getModel ("users");
		$model->clear_thumb ();

		$url = JUri::base (true) . '/index.php?option=com_jlexcomment&view=users';
		$this->setRedirect ($url, JText::_("JCM_THUMBNAILS_REMOVED"));
	}

	public function clearcm ()
	{
		$model = $this->getModel ("users");
		$model->clear_comment ();

		$url = JUri::base (true) . '/index.php?option=com_jlexcomment&view=users';
		$this->setRedirect ($url, JText::_("JCM_COMMENT_OF_USER_REMOVED"));
	}
}
