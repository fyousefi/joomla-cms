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
	public function edit ()
	{
		$app 	= JFactory::getApplication();
		$id 	= $app->input->getInt ('id', 0);

		$this->addModelPath ( JPATH_ROOT . '/administrator/components/com_jlexcomment/models', 'JLexComment' );

		$model = $this->getModel ('item');
		$model->set ('id', $id );

		$row = $model->getItem ();

		if (! $row)
		{
			$response = array (
				'status' => 400,
				'error'  => $model->getError ()
			);
		} else {
			// remove some variables
			if ($row->sticker_id > 0)
			{
				$row->sticker = new stdClass();
				$row->sticker->id = $row->sticker_id;
				$row->sticker->url = JUri::root(true) . '/' . $row->sk_path2file;
			}

			if (is_array($row->media) && count($row->media) )
			{
				foreach ($row->media as $k=>&$media)
				{
					unset ($media->comment_id);
					unset ($media->created);
					unset ($media->created_by);
					unset ($media->path);
					unset ($media->fileName);
				}
			}

			$row->member = true;
			if ( !$row->created_by )
			{
				$row->member = false;
			}

			unset ($row->obj_id);
			unset ($row->child_count);
			unset ($row->child_count_active);
			unset ($row->up_point);
			unset ($row->down_point);
			unset ($row->report_count);
			unset ($row->created_by);
			unset ($row->created_time);
			unset ($row->modified_by);
			unset ($row->modified_time);
			unset ($row->published);
			unset ($row->featured);
			unset ($row->ip_address);
			unset ($row->language);
			unset ($row->sticker_id);
			unset ($row->sk_path2file);
			unset ($row->created_name);
			unset ($row->modified_name);
			unset ($row->sent);

			$response = array (
				'status' => 200,
				'data'  => $row
			);
		}
		
		JLexCommentHelper::mix2json ($response);
	}

	public function save()
	{
		$app 	= JFactory::getApplication();
		$post 	= $app->input->post;
		$user 	= JFactory::getUser();
		$session= JFactory::getSession();
		$config = JLexCommentHelper::getConfig ();

		$key 	= $post->getString ('key', '');
		$parse 	= JLexCommentHelper::decodeSub ( $key );

		if (! $parse)
		{
			$response = array (
				'status' => 400,
				'error'  => "Key incorrect."
			);
			JLexCommentHelper::mix2json ($response);
		}

		$this->addModelPath ( JPATH_ROOT . '/administrator/components/com_jlexcomment/models', 'JLexComment' );

		$model = $this->getModel ('item');
		$model->set ('com_name', $parse->com_name);
		$model->set ('com_key', $parse->com_key);
		$model->set ('id', $post->getInt('id',0) );

		$return = $model->save ();
		if (! $return)
		{
			$response = array (
				'status' => 400,
				'error'  => $model->getError ()
			);
		} else {
			// auto suggest subscribe
			$isOn = $post->getBool('__sub',false);
			$subResponse = 0;

			if ($isOn)
			{
				$subData = array(
						'key' => $key,
						'name' => '',
						'email' => ''
					);

				if ($user->guest)
				{
					$subData['name'] 	= $post->getString('guest_name','');
					$subData['email'] 	= $post->getString('guest_email','');
				} else {
					$subData['name'] 	= $user->name;
					$subData['email'] 	= $user->email;
				}

				$subModel 	= $this->getModel('subscribe');
				$subReturn 	= $subModel->add ($subData);

				if ($subReturn==false)
				{
					$subResponse = array (
							'status' => 400,
							'error'  => $subModel->getError ()
						);
				} else {
					$subResponse = array (
							'status' => 200,
							'hash' => $subReturn,
							'verifyRequire' => ($user->guest&&$config->def("email_verify",0)==1?1:0),
							'email' => $subData['email']
						);
				}
			}

			$response = array (
				'status' 	=> 200,
				'id'  		=> $return,
				'sub' 		=> $subResponse
			);

			// set latest_cm
			$session->set('latest_cm',$return*1);
		}

		JLexCommentHelper::mix2json ($response);
	}

	public function vote()
	{
		$app 	= JFactory::getApplication();
		$id 	= $app->input->getInt ('id', 0);

		$this->addModelPath ( JPATH_ROOT . '/administrator/components/com_jlexcomment/models', 'JLexComment' );

		$model = $this->getModel ('item');
		$model->set ('id', $id );

		$return = $model->vote ();

		if (! $return)
		{
			$response = array (
					'status' => 400,
					'error' => $model->getError ()
				);
		} else {
			$response = array (
					'status' => 200,
					'data' => $return
				);
		}

		JLexCommentHelper::mix2json ($response);
	}

	private function _quick_action($action, $json=true)
	{
		$app 	= JFactory::getApplication();
		$id 	= $app->input->getInt ('id', 0);

		$this->addModelPath ( JPATH_ROOT . '/administrator/components/com_jlexcomment/models', 'JLexComment' );

		$model = $this->getModel ('item');
		$model->set ('id', $id );

		switch ( $action ) {
			case 'remove':
				$return = $model->remove ();
				break;

			case 'state':
				$return = $model->state ();
				break;
			
			default:
				$return = $model->feature ();
				break;
		}

		if ($json==false)
		{
			if (!$return)
			{
				$app->enqueueMessage ($model->getError(), "error");
				return false;
			}

			return true;
		}

		if (! $return)
		{
			$response = array (
					'status' => 400,
					'error' => $model->getError ()
				);
		} else {
			$response = array (
					'status' => 200,
					'data' => $return
				);
		}

		JLexCommentHelper::mix2json ($response);
	}

	public function remove ()
	{
		$this->_quick_action ('remove');
	}

	public function state ()
	{
		$this->_quick_action ('state');
	}

	public function feature ()
	{
		$this->_quick_action ('feature');
	}

	public function next()
	{
		$app 	= JFactory::getApplication();
		$do  	= $app->input->getCmd("do");
		$id  	= $app->input->getInt("id");
		$hash 	= $app->input->get("hash","","raw");
		$prefix = JFactory::getDbo()->getPrefix();

		$hashCorrect = md5('jcm-quicklink--' . $prefix . '--' . $id);

		if ($hash!=$hashCorrect || $id<1)
		{
			throw new Exception(JText::_("JCM_PERMISSION_DENIED"), 403);
			return false;
		}

		$config = JLexCommentHelper::getConfig ();

		// get url return
		$url 	= JUri::base();
		$db 	= JFactory::getDbo();
		$query 	= $db->getQuery(true);
		$query->select('obj_id')
			  ->from('#__jlexcomment')
			  ->where('id=' . $id);

		$objId 	= $db->setQuery($query)->loadResult();
		if($objId)
		{
			$object = JLexCommentHelper::getObjDetail($objId);
			if($object->url!='')
			{
				$url = $object->url;
				if($do!='delete')
				{
					$url.= (preg_match('/\?/', $url)?'&':'?') . 'comment_id=' . $id;
				}
			}
		}
		

		switch ($do)
		{
			case "publish":
			case "unpublish":
				$config->set("u_state_any_comment", true);
				$app->input->set("state", $do=="publish"?1:0);
				$return  = $this->_quick_action ('state',false);

				if ($return===false)
				{
					$app->redirect ($url);
				} else {
					$msg = $do=="publish" ? JText::sprintf("JCM_ROWS_PUBLISHED_COUNT","1") : JText::sprintf("JCM_ROWS_UNPUBLISHED_COUNT","1");
					$this->setRedirect( $url, $msg);
				}
				break;

			case "delete":
				$config->set("u_del_any_comment", true);
				$return = $this->_quick_action ('remove',false);

				if ($return===false)
				{
					$app->redirect ($url);
				} else {
					$this->setRedirect($url, JText::sprintf("JCM_ROWS_DELETED_COUNT","1"));
				}
				break;

			case "featured":
				$config->set("u_feature_any_comment", true);
				$return = $this->_quick_action ('feature',false);

				if ($return===false)
				{
					$app->redirect ($url);
				} else {
					$this->setRedirect($url, JText::_("JCM_ROWS_FEATURED"));
				}
				break;

			default:
				throw new Exception(JText::_("JCM_PERMISSION_DENIED"), 403);
				break;
		}
	}

	public function cin()
	{
		$app = JFactory::getApplication();
		$key = $app->input->getString('key', '');
		$cid  = $app->input->getString('cid', '');

		$parse = JLexCommentHelper::decodeSub($key);

		if(!$parse || !preg_match('/^(([1-9][0-9]*),?)+$/', $cid))
		{
			JLexCommentHelper::mix2json([
				'status' => 400,
				'error'  => 'Bad request.'
			]);
		}


    	$db = JFactory::getDbo();
    	$query = $db->getQuery(true);

    	$query->select('id')
    		  ->from('#__jlexcomment_obj')
    		  ->where([
    		  	'com_name='.$db->quote($parse->com_name),
    		  	'com_key='.$db->quote($parse->com_key)
    		  ]);

    	$oid = $db->setQuery($query)->loadResult();

    	if(!$oid)
    	{
    		JLexCommentHelper::mix2json([
				'status' => 400,
				'error'  => 'Bad request.'
			]);
    	}

    	$cid = trim($cid, ',');
    	$query->clear()
    		  ->select('id, IF(obj_id='.$db->quote($oid).',1,0) AS sf')
    		  ->from('#__jlexcomment')
    		  ->where('id IN('.$cid.')');

    	$rows = $db->setQuery($query)->loadObjectList();

    	JLexCommentHelper::mix2json([
			'status' => 200,
			'data'  => $rows
		]);
	}
}
