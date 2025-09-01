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

class JLexCommentControllerSubscription extends JControllerLegacy
{
	public function add ()
	{
		$url = JUri::base (true) . '/index.php?option=com_jlexcomment&view=subscription&layout=form';
		$this->setRedirect ($url);
	}

	public function cancel ()
	{
		$url = JUri::base (true) . '/index.php?option=com_jlexcomment&view=subscription';
		$this->setRedirect ($url);
	}

	public function save ()
	{
		$app 	= JFactory::getApplication ();
		$model 	= $this->getModel ('subscription');
		$return = $model->save ();

		if (! $return)
		{
			$id = $app->input->getInt ("id", 0);

			$url = JUri::base (true) . '/index.php?option=com_jlexcomment&view=subscription&layout=form' . ($id>0?"&id=".$id:"");

			$this->setRedirect ($url, $model->getError(), "error");
			return false;
		}

		$url = JUri::base (true) . '/index.php?option=com_jlexcomment&view=subscription';
		$this->setRedirect ($url, JText::_("JCM_ROWS_SAVED"));
	}

	public function enable ()
	{
		$model = $this->getModel ('subscription');
		$model->set ('jcm_state', 1);

		$model->state();

		$url = JUri::base (true) . '/index.php?option=com_jlexcomment&view=subscription';
		$this->setRedirect ($url, JText::_("JCM_EMAILS_VERIFIED"));
	}

	public function disable ()
	{
		$model = $this->getModel ('subscription');
		$model->set('jcm_state', 0);

		$model->state();

		$url = JUri::base (true) . '/index.php?option=com_jlexcomment&view=subscription';
		$this->setRedirect ($url, JText::_("JCM_EMAILS_UNCONFIRMED"));
	}

	public function delete ()
	{
		$model = $this->getModel ('subscription');
		$return = $model->remove ();

		$msg = JText::_("JCM_ROWS_DELETED");

		$url = JUri::base (true) . '/index.php?option=com_jlexcomment&view=subscription';
		$this->setRedirect ($url, $msg);
	}

	public function publish()
	{
		$model = $this->getModel('subscription');
		$model->switch_status(1);

		$url = JUri::base (true) . '/index.php?option=com_jlexcomment&view=subscription';
		$this->setRedirect ($url, JText::_("JCM_ADMIN_SWITCH_TO_SUBSCRIBED"));
	}

	public function unpublish()
	{
		$model = $this->getModel('subscription');
		$model->switch_status(0);

		$url = JUri::base (true) . '/index.php?option=com_jlexcomment&view=subscription';
		$this->setRedirect ($url, JText::_("JCM_ADMIN_SWITCH_TO_UNSUBSCRIBE"));
	}
}
