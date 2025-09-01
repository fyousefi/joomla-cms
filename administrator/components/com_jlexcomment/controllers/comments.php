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

class JLexCommentControllerComments extends JControllerLegacy
{
	public function add()
	{
		$url = JUri::base (true) . '/index.php?option=com_jlexcomment&view=item';
		$this->setRedirect ($url);
	}

	public function publish()
	{
		$this->state(1);
	}

	public function unpublish()
	{
		$this->state(0);
	}

	private function state($state = 1)
	{
		$model = $this->getModel ('comments');
		$model->set('jcm_state', $state);

		$return = $model->state ();
		$url = $this->_getUrl ();

		if ($return==false)
		{
			$this->setRedirect ($url, $model->getError());
		} else {
			$msg = $state==1 ? JText::sprintf("JCM_ROWS_PUBLISHED_COUNT", $return['success'] . '/' . $return['total']) : JText::sprintf("JCM_ROWS_UNPUBLISHED_COUNT", $return['success'] . '/' . $return['total']);
			$this->setRedirect ($url, $msg);
		}
	}

	public function feature ()
	{
		$this->_feature (1);
	}

	public function unfeature ()
	{
		$this->_feature (0);
	}

	private function _feature($state = 1)
	{
		$model = $this->getModel('comments');
		$model->set('jcm_state', $state);

		$return = $model->feature ();
		$url = $this->_getUrl ();

		if ($return==false)
		{
			$this->setRedirect ($url, $model->getError());
		} else {
			$msg = $state==1 ? JText::_("JCM_ROWS_FEATURED") : JText::_("JCM_ROWS_UNFEATURED");
			$this->setRedirect ($url, "These rows " . ($state==1?"featured":"unfeatured"));
		}
	}

	private function _getUrl ()
	{
		$app = JFactory::getApplication ();
		$url = JUri::base (true) . '/index.php?option=com_jlexcomment&view=comments';

		if ($app->input->getCmd('layout','default')!='default')
		{
			$url.= '&layout=' . $app->input->getCmd ('layout');
		}

		if ($app->input->getCmd('tmpl')=='component')
		{
			$url.= '&tmpl=component';
		}

		if ($app->input->getCmd('function','')!='')
		{
			$url.= '&function=' . $app->input->getCmd('function');
		}

		return $url;
	}

	public function delete ()
	{
		$model = $this->getModel('comments');
		$return = $model->remove();

		$msg = JText::sprintf("JCM_ROWS_DELETED_COUNT", $return['success'] . '/' .$return['total'] );

		$url = JUri::base (true) . '/index.php?option=com_jlexcomment&view=comments';
		$this->setRedirect ($url, $msg);
	}
}
