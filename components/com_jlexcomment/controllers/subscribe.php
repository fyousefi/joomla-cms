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

class JLexCommentControllerSubscribe extends JControllerLegacy
{
	public function suggest ()
	{
		$model = $this->getModel ('subscribe');

		$suggest = $model->suggest ();

		if ($suggest==false)
		{
			JLexCommentHelper::mix2json (array(
					'status' => 400,
					'error'	 => $model->getError ()
				));
		}

		JLexCommentHelper::mix2json (array(
					'status' => 200,
					'data'	 => $suggest
		));
	}

	public function add ()
	{
		$model = $this->getModel ('subscribe');
		$return = $model->add ();

		if ($return==false)
		{
			$response = array (
					'status' => 400,
					'error'  => $model->getError ()
				);
		} else {
			$response = array (
					'status' => 200,
					'hash' => $return
				);
		}

		JLexCommentHelper::mix2json ( $response );
	}

	public function remove ()
	{
		$model = $this->getModel ('subscribe');
		$return = $model->remove ();

		if (! $return)
		{
			$response = array (
					'status' => 400,
					'error'  => $model->getError ()
				);
		} else {
			$response = array (
					'status' => 200
				);
		}

		JLexCommentHelper::mix2json ( $response );
	}

	public function activation ()
	{
		$model = $this->getModel ('subscribe');
		$return = $model->activation ();

		$url = JUri::root ();

		if (! $return)
		{
			$this->setRedirect ($url, $model->getError(), "error");
			return;
		}

		$this->setRedirect ($return, JText::_("JCM_SUBSCRIBE_MSG"));
	}

	public function mail ()
	{
		$app = JFactory::getApplication();

		$client_ip = getenv('HTTP_CLIENT_IP')?:
						getenv('HTTP_X_FORWARDED_FOR')?:
						getenv('HTTP_X_FORWARDED')?:
						getenv('HTTP_FORWARDED_FOR')?:
						getenv('HTTP_FORWARDED')?:
						getenv('REMOTE_ADDR');

		$server_ip = $_SERVER['SERVER_ADDR'];

		if ($client_ip != $server_ip)
		{
			/*$app->close("Failure");*/
		}

		$model = $this->getModel ("subscribe");
		$model->mail ();
		$model->mailq ();
		$model->mailq_admin ();

		$app->close("Success");
	}

	public function sync($offset=0)
	{
		$app = JFactory::getApplication();

		$client_ip = getenv('HTTP_CLIENT_IP')?:
						getenv('HTTP_X_FORWARDED_FOR')?:
						getenv('HTTP_X_FORWARDED')?:
						getenv('HTTP_FORWARDED_FOR')?:
						getenv('HTTP_FORWARDED')?:
						getenv('REMOTE_ADDR');

		$server_ip = $_SERVER['SERVER_ADDR'];

		if($client_ip != $server_ip)
		{
			/*$app->close("Failure");*/
		}

		JModelLegacy::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_jlexcomment/models', 'JLexCommentModel');

		$model = $this->getModel("sync");
		$model->set('offset', $offset);
		$result = $model->sync();

		if($result->total>$result->offset+$result->limit){
			// continue
			$this->sync($result->offset+$result->limit);
		} else {
			// complete
			JLexCommentHelper::mix2json($result);
		}
	}

	public function task()
	{
		$app = JFactory::getApplication();
		$config = JLexCommentHelper::getConfig();

		$maxd=(int) $config->get('unpublish_downvote_act', 0);
		$maxr=(int) $config->get('unpublish_report_act', 0);

		if($maxd>0 || $maxr>0)
		{
			$db = JFactory::getDbo();
			$query = $db->getQuery(true);

			$query->update('#__jlexcomment')
				  ->set('published=0')
				  ->where('published=1');

			$orClauses=[];
			if($maxd>0) $orClauses[]='down_point>='.$db->quote($maxd);
			if($maxr>0) $orClauses[]='report_count>='.$db->quote($maxr);

			$query->where('('.implode(' OR ', $orClauses).')');

			$db->setQuery($query)->execute();
		}

		JLexCommentHelper::mix2json(['status'=>200]);
	}

	/*public function mailq ()
	{
		// run from Cli
		if ( php_sapi_name() !== 'cli')
		{
			return false;
		}

		$model = $this->getModel ("subscribe");
		$model->mailq ();
	}

	public function mailq_admin ()
	{
		// run from Cli
		if ( php_sapi_name() !== 'cli')
		{
			return false;
		}

		$model = $this->getModel ("subscribe");
		$model->mailq_admin ();
	}*/

	public function stop ()
	{
		$model = $this->getModel ("subscribe");
		$return = $model->stop ();

		$url = JUri::root ();

		if (! $return)
		{
			$this->setRedirect ($url, $model->getError(), "error");
			return;
		}

		$this->setRedirect ($url, JText::_("JCM_UNSUBSCRIBE_MSG_EMAIL"));
	}
}
