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

class JLexCommentControllerReplacer extends JControllerLegacy
{
	public function add ()
	{
		$url = JUri::base (true) . '/index.php?option=com_jlexcomment&view=replacer&layout=form';
		$this->setRedirect ($url);
	}

	public function cancel ()
	{
		$url = JUri::base (true) . '/index.php?option=com_jlexcomment&view=replacer';
		$this->setRedirect ($url);
	}

	public function save ()
	{
		$model = $this->getModel ('replacer');
		$return = $model->save ();

		if (! $return)
		{
			$url = JUri::base (true) . '/index.php?option=com_jlexcomment&view=replacer&layout=form';
			$this->setRedirect ($url, $model->getError(), "error");
			return false;
		}

		$url = JUri::base (true) . '/index.php?option=com_jlexcomment&view=replacer';
		$this->setRedirect ($url, JText::_("JCM_ROWS_SAVED"));
	}

	public function publish ()
	{
		$model = $this->getModel ('replacer');
		$model->set ('jcm_state', 1);

		$model->state ();

		$url = JUri::base (true) . '/index.php?option=com_jlexcomment&view=replacer';
		$this->setRedirect ($url, JText::_("JCM_ROWS_PUBLISHED"));
	}

	public function unpublish ()
	{
		$model = $this->getModel ('replacer');
		$model->set ('jcm_state', 0);

		$model->state ();

		$url = JUri::base (true) . '/index.php?option=com_jlexcomment&view=replacer';
		$this->setRedirect ($url, JText::_("JCM_ROWS_UNPUBLISHED"));
	}

	public function delete ()
	{
		$model = $this->getModel ('replacer');
		$return = $model->remove ();

		$msg = JText::_("JCM_ROWS_DELETED");
		
		$url = JUri::base (true) . '/index.php?option=com_jlexcomment&view=replacer';
		$this->setRedirect ($url, $msg);
	}
}
