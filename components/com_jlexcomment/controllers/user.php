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

class JLexCommentControllerUser extends JControllerLegacy
{
	public function thumb ()
	{
		$app = JFactory::getApplication();
		$request = $app->input;

		$model = $this->getModel ('user');

		switch ($request->getCmd ('action','load'))
		{
			case 'upload':
				$return = $model->upload_thumb ();
				if (! $return)
				{
					$response = array (
						'status' => 400,
						'error'  => $model->getError ()
					);
				} else {
					$response = array (
						'status' => 200,
						'url' => $return
					);
				}

				JLexCommentHelper::mix2json ( $response );
				break;

			case 'remove':
				$return = $model->remove_thumb ();
				JLexCommentHelper::mix2json ( array(
					'status' => 200
				));

				break;

			default:
				$return = $model->load_thumb ();
				JLexCommentHelper::mix2json (array(
					'status' => 200,
					'url' => $return
				));
				break;
		}
	}

	public function list_notification ()
	{
		$app = JFactory::getApplication();
		$user = JFactory::getUser ();

		// subscribe
		if(!$user->guest)
		{
			try {
				$modelHelper = $this->getModel('subscribe');
				$modelHelper->mail($user->id);
			} catch(Exception $e){};
		}

		$model = $this->getModel ('user');
		$model->set ('offset', $app->input->getInt ('offset',0));
		$notifications = $model->list_notifications ();

		JLexCommentHelper::mix2json ($notifications);
	}

	public function profile ()
	{
		$app = JFactory::getApplication();
		$uid = $app->input->getInt ('uid', 0);

		$model = $this->getModel ('user');
		$model->set ('uid', $uid);
		$return = $model->getUser ();

		JLexCommentHelper::mix2json ($return);
	}

	public function list_comments ()
	{
		$app = JFactory::getApplication();
		$uid = $app->input->getInt ('uid', 0);
		$offset = $app->input->getInt ('offset', 0);

		$model = $this->getModel ('user');
		$model->set ('uid', $uid);
		$model->set ('offset', $offset>0?$offset:0 );

		$return = $model->list_comment ();

		if ( $return==false)
		{
			$response = array (
				'status' => 400,
				'error'	=> $model->getError ()
			);
		} else {
			$response = array (
				'status' => 200,
				'data' => $return
			);
		}

		JLexCommentHelper::mix2json ($response);
	}

	public function seen ()
	{
		$app = JFactory::getApplication();
		$id  = $app->input->getInt ('id', 0);

		$model = $this->getModel ('user');
		$model->set ('id', $id);
		$model->seen ();
	}

	public function offmail ()
	{
		$app = JFactory::getApplication ();
		$model = $this->getModel ('user');
		$return = $model->offmail ();

		if ($app->input->getBool('ajax',0)==1)
		{
			if ($return==false)
			{
				$response = array (
					'status' => 400,
					'error' => $model->getError ()
				);
			} else {
				$response = array (
					'status' => 200
				);
			}

			JLexCommentHelper::mix2json ($response);
			return;
		}

		// for simple request.
		$url = JUri::root ();

		if ($return==false)
		{
			$this->setRedirect ($url, $model->getError(), "error");
			return false;
		}

		$this->setRedirect ($url, JText::_("JCM_UNSUBSCRIBE_MSG_EMAIL"));
	}

	public function onmail ()
	{
		$app = JFactory::getApplication ();
		$model = $this->getModel ('user');
		$return = $model->onmail ();

		if ($app->input->getBool('ajax',0)==1)
		{
			if ($return==false)
			{
				$response = array (
					'status' => 400,
					'error' => $model->getError ()
				);
			} else {
				$response = array (
					'status' => 200
				);
			}

			JLexCommentHelper::mix2json ($response);
			return;
		}

		$url = JUri::root ();

		if ($return==false)
		{
			$this->setRedirect ($url, $model->getError(), "error");
			return false;
		}

		$this->setRedirect ($url, JText::_("JCM_UNSUBSCRIBE_MSG_EMAIL"));
	}

	public function mark_read ()
	{
		$model = $this->getModel ('user');
		$return = $model->mark_read ();

		JLexCommentHelper::mix2json (array ('status' => 200));
	}

	public function login ()
	{
		$app = JFactory::getApplication();

		$model = $this->getModel ('user');
		$return = $model->login ();

		if ( $return==false )
		{
			$response = array ('status'=>400, 'error'=>$model->getError());
		} else {
			$response = array ('status'=>200);
		}

		JLexCommentHelper::mix2json ($response);
	}

	public function logout ()
	{
		$app = JFactory::getApplication();
		$app->logout ();
	}

	public function oauth ()
	{
		$app = JFactory::getApplication();
		$app->input->set ('tmpl', 'component');

		$model = $this->getModel ('user');
		$return = $model->oauth ();

		if ( !$return)
		{
			$app->enqueueMessage ( $model->getError() , 'error');
			return false;
		} else {
			$js = '<html><head>';
			$js .= '<script data-cfasync="false" type="text/javascript">';
				$js .= 'window.setTimeout(function(){';
					$js .= 'window.opener.location.reload(false);';
					$js .= 'window.close();';
				$js .= '},1000);';
			$js .= '</script></head>';
			$js .= '<body><p>Just moment...</p></body></html>';
			echo $js;
			$app->close ();
		}
	}

	public function oauth_issue()
	{
		$app = JFactory::getApplication();
		$msg = $app->input->getString('issue_msg', '');

		$js = '<html><head>';
		$js .= '<script data-cfasync="false" type="text/javascript">';
			$js .= 'window.setTimeout(function(){';
				if($msg!='')
				{
					
				}
				$js .= 'window.close();';
			$js .= '},1000);';
		$js .= '</script></head>';
		$js .= '<body></body></html>';
		echo $js;
		$app->close ();
	}

	public function activity()
	{
		$app   = JFactory::getApplication();
		$doc   = JFactory::getDocument();
		$view  = $this->getView("user", "html");
		$model = $this->getModel("user");
		$uid   = $this->get("uid",0)<1 ? $app->input->getInt("id",0) : $this->get("uid");
		$uid   = intval($uid);
		$limit = 10;
		$offset= $app->input->getInt("limitstart",0);

		if ($uid<1)
		{
			throw new Exception(JText::_("JCM_PAGE_NOT_FOUND"), 404);
			
			return false;
		}

		$doc->addStylesheet( JUri::base(true) . "/components/com_jlexcomment/assets/3rd.css");

		$model->set("uid", $uid);
		$model->set("offset", $offset);

		$activity = $model->list_comment();
		$activity->user = JFactory::getUser($uid);
		//$activity->notifications = $model->list_notifications();

		// pagination for comment
		jimport ( 'joomla.html.pagination' );
        $activity->pagination = new JPagination ( $activity->total, $offset, $limit );


		$view->setModel($model);
		$view->set("activity", $activity);

		return $view->render();
	}

	public function getAvatar()
	{
		$app = JFactory::getApplication();
		$uid = $app->input->getInt("id", 0);

		$config = JLexCommentHelper::getConfig();
        $default = JUri::root().$config->get("default_avatar", "media/jcm/avatar/jcm_avatar.png");

        $thumb = null;

		if($uid>0 && preg_match("/^[1-9][0-9]*$/", $config->get("user_field_id", 0)))
		{
			JLoader::register('FieldsHelper', JPATH_ADMINISTRATOR . '/components/com_fields/helpers/fields.php');
			$fields = FieldsHelper::getFields('com_users.user', JFactory::getUser($uid), true);

			if(!empty($fields))
			{
				foreach($fields as $field)
				{
					if($field->id==(int) $config->get("user_field_id"))
					{
						$thumb = strip_tags($field->rawvalue?$field->rawvalue:$field->value);

						if(preg_match("/\{/", $thumb))
						{
							// json format
							$thumb = json_decode($thumb);
							$thumb = $thumb[0]->value;
						}

						$ext = strtolower(pathinfo($thumb, PATHINFO_EXTENSION));

						if(in_array($ext, ["jpg", "jpeg", "png", "gif"]))
						{
							$thumb = JPATH_ROOT."/".trim($thumb, "/");
							if(file_exists($thumb))
							{
								// create thumb
								$nfile = md5($thumb).".jpg";
								$url = JUri::base(true)."/media/jcm/avatar/".$nfile;

								if(file_exists(JPATH_ROOT."/media/jcm/avatar/".$nfile))
								{
									// redirect to this file
									$app->redirect($url);
								} else {
									// create thumb
									require_once JPATH_ADMINISTRATOR."/components/com_jlexcomment/libraries/class.image.php";

									$resizeObj = new abeautifulsite\SimpleImage($thumb);

									try {
							            $nthumb = JPATH_ROOT."/media/jcm/avatar/".$nfile;
							            $resizeObj->thumbnail(160, 160)
							                        ->save($nthumb);
							        } catch (Exception $e) {}

							        $app->redirect($url);
								}
							}
						}

						break;
					}
				}
			}
		}

		$app->redirect($default);
	}
}
