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

class JLexCommentControllerItem extends JControllerLegacy
{
	public function apply()
	{
		$this->_store(true);
	}

	public function save()
	{
		$this->_store(false);
	}

	public function cancel ()
	{
		$app = JFactory::getApplication ();
		$session = JFactory::getSession ();

		$id  = $app->input->get ("id", 0);
		if (!$id)
		{
			$session->set ("jcm_comment_tmp", null);
		}
		
		$url = JUri::base(true) . "/index.php?option=com_jlexcomment&view=comments";
		$this->setRedirect ($url);
	}

	private function _store($apply=false)
	{
		$app = JFactory::getApplication();
		$id  = $app->input->get("id", 0);
		$session = JFactory::getSession();

		$model = $this->getModel ("item");
		$model->set('id', $id);
		
		$return = $model->save ();

		$url = JUri::base(true) . "/index.php?option=com_jlexcomment&view=item" . ($id>0?"&id={$id}":"");
		$urlCms = JUri::base(true) . "/index.php?option=com_jlexcomment&view=comments";

		if($return==false)
		{
			// create tmp data if it's new item
			if(!$id)
			{
				$session->set("jcm_comment_tmp", $app->input->post->getArray($_POST));
			}

			$this->setRedirect($url, $model->getError(), "error");
			return false;
		}

		$session->clear("jcm_comment_tmp");

		if($apply)
		{
			$url = JUri::base(true) . "/index.php?option=com_jlexcomment&view=item&id=" . $return;
			$this->setRedirect ($url, ($id==$return?JText::_("JCM_COMMENT_SAVED"):JText::_("JCM_COMMENT_ADDED")) );
		} else {
			$this->setRedirect ($urlCms, JText::_("JCM_COMMENT_SAVED"));
		}
	}
}
