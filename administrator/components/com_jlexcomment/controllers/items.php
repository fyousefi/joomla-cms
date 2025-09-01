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

class JLexCommentControllerItems extends JControllerLegacy
{
	public function add ()
	{
		$url = JUri::base (true) . '/index.php?option=com_jlexcomment&view=items&layout=form';
		$this->setRedirect ($url);
	}

	public function save()
	{
		$model = $this->getModel ('items');
		$return = $model->save ();

		if (! $return)
		{
			$url = JUri::base (true) . '/index.php?option=com_jlexcomment&view=items&layout=form';
			$this->setRedirect ($url, $model->getError(), "error");
			return false;
		}

		$url = JUri::base (true) . '/index.php?option=com_jlexcomment&view=items';
		$this->setRedirect ($url, JText::_("JCM_ENTRY_SAVED"));
	}

	public function publish ()
	{
		$model = $this->getModel ('items');
		$model->set('jcm_state', 1);

		$model->state();

		$url = JUri::base (true) . '/index.php?option=com_jlexcomment&view=items';
		$this->setRedirect ($url, JText::_("JCM_ROWS_PUBLISHED"));
	}

	public function unpublish ()
	{
		$model = $this->getModel ('items');
		$model->set ('jcm_state', 0);

		$model->state();

		$url = JUri::base (true) . '/index.php?option=com_jlexcomment&view=items';
		$this->setRedirect ($url, JText::_("JCM_ROWS_UNPUBLISHED"));
	}

	public function delete ()
	{
		$model = $this->getModel ('items');
		$return = $model->remove();

		$msg = JText::sprintf("JCM_ROWS_DELETED_COUNT", $return['success'] . '/' .$return['total'] );

		$url = JUri::base (true) . '/index.php?option=com_jlexcomment&view=items';
		$this->setRedirect ($url, $msg);
	}

	public function truncate ()
	{
		$model = $this->getModel('items');
		$model->truncate();

		$url = JUri::base (true) . '/index.php?option=com_jlexcomment&view=items';
		$this->setRedirect ($url, JText::_("JCM_ENTRY_TRUNCATE_COMMENT"));
	}

	public function recalculate ()
	{
		$model = $this->getModel ('items');
		$model->recalculate ();

		$url = JUri::base (true) . '/index.php?option=com_jlexcomment&view=items';
		$this->setRedirect ($url, JText::_("JCM_ENTRY_RECALCULATE_COMMENT"));
	}

	public function cancel ()
	{
		$url = JUri::base (true) . "/index.php?option=com_jlexcomment&view=items";
		$this->setRedirect ($url);
	}
}
