<?php
/**
 * @package     JLex Comment
 * @version     1.0.0
 * @copyright   Copyright (C) 2013-2016 JLexArt Team (http://www.jlexart.com). All rights reserved.
 * @license     GNU/GPL or later
 * @author      JLexArt (support@jlexart.com)
 */

defined ( '_JEXEC' ) or die ();

jimport ('joomla.filesystem.file');

// using to create thumb image
require_once JPATH_ROOT.'/administrator/components/com_jlexcomment/libraries/class.image.php';

class JLexCommentModelUser extends JModelLegacy
{
	public function upload_thumb ()
	{
		$app     = JFactory:: getApplication ();
		$request = $app->input->post;
        $user    = JFactory::getUser ();
        $time    = JFactory::getDate()->toSql();

        $fileAllows = array(
                'jpg', // image type
                'jpeg',
                'png',
                'gif'
            );

        $file = array_key_exists('file', $_FILES) ? $_FILES['file'] : null;

        if ($file == null || $file['size'] == 0 || $file['error'] != 0)
        {
            $this->setError( JText::_("JCM_SELECT_FILE_TO_UPLOAD") );
            return false;
        }

        $config  = JLexCommentHelper::getConfig();
        $filesize = (int) $config->get("media_filesize", 1);

        if ($file['size'] > $filesize * 1024 * 1024)
        {
            $this->setError( JText::sprintf("JCM_MAX_FILE_SIZE_ALLOW_IS", $filesize));
            return false;
        }

        $type = strtolower( JFile::getExt ($file['name']) );

        if (! in_array($type, $fileAllows))
        {
            $this->setError( JText::_("JCM_FILE_NOT_SUPPORTED") );
            return false;
        }


        $filename_new = substr(md5 ($file ['name'] . $time . $user->id), 0, 16) . '.' . $type;

        if ( ! @is_array(getimagesize($file['tmp_name'])) )
        {
            $this->setError (JText::_("JCM_FILE_NOT_IMAGE"));
            return false;
        }

        // create thumbnail image
        $resizeObj = new abeautifulsite\SimpleImage ( $file['tmp_name'] );

        try {
            $thumb = JPATH_ROOT . '/media/jcm/avatar/' . $filename_new;
            $resizeObj->auto_orient()->crop(	$request->getInt('x',0),
            					$request->getInt('y',0),
            					$request->getInt('x',0) + $request->getInt('width',0),
            					$request->getInt('y',0) + $request->getInt('height',0))
            			->resize (120, 120)
            			->save ($thumb);
        } catch (Exception $e) {
            $this->setError ($e->getMessage());
            return false;
        }

        // update database
        $query = "
        	INSERT INTO #__jlexcomment_users (userid, created, auth, auth_id, auth_url, auth_picture) VALUES ({$user->id}, '{$time}', 'joomla', '' , '' ,'{$filename_new}')
  				ON DUPLICATE KEY UPDATE auth=VALUES(auth), auth_picture=VALUES(auth_picture);
        ";

        $this->_db->setQuery($query)->execute();

        $url = JUri::base (true) . '/media/jcm/avatar/' . $filename_new;
        return $url;
	}

	public function remove_thumb ()
	{
		$user = JFactory::getUser ();

		$query = "SELECT auth_picture FROM #__jlexcomment_users WHERE userid=" . $user->id;
		$result = $this->getDbo()->setQuery ($query)->loadResult ();

		if ( $result )
		{
			// delete file
			$file = JPATH_ROOT . '/media/jcm/avatar/' . $result;
			if ( JFile::exists($file) )
			{
				JFile::delete ($file);
			}
		}

		$query = "DELETE FROM #__jlexcomment_users WHERE userid=" . $user->id;
        $this->getDbo()->setQuery ($query)->execute();

        return true;
	}

	public function load_thumb ()
	{
		$user = JFactory::getUser ();
		$query = "SELECT auth_picture FROM #__jlexcomment_users WHERE userid=" . $user->id;
		$thumb = $this->getDbo()->setQuery ($query, 0, 1)->loadResult ();

		if ( $thumb )
		{
			if (!preg_match("/^https?:\/\//", $thumb))
				$thumb = JUri::base(true) . '/media/jcm/avatar/' . $thumb;
		}

		return $thumb;
	}


	public function list_notifications ()
	{
		$app = JFactory::getApplication ();
		$config = JLexCommentHelper::getConfig ();
		$request = $app->input;

		$user = JFactory::getUser ();
		$query = $this->getDbo()->getQuery (true);

		$result = new stdClass ();
		$result->offset = $request->getInt('offset',0) > 0 ? $request->getInt('offset') : 0;
		$result->limit  = 10;

		$whereClauses = array ();
		$whereClauses[] = "obj.published=1";
		$whereClauses[] = "nof.user_remind=" . $user->id;
		$whereClauses[] = "cm.published=1";

		$query->select ("SQL_CALC_FOUND_ROWS nof.*")
			  ->select ("COUNT(nof.id) count_ppl")
			  ->select ("MAX(nof.created_time) created_time")
			  ->from ("#__jlexcomment_notification AS nof")

			  ->select ("obj.title AS object_name, obj.url AS object_url, obj.com_name, obj.com_key")
			  ->innerJoin ("#__jlexcomment_obj AS obj ON nof.obj_id=obj.id")

			  ->select ("GROUP_CONCAT( IF(nof.created_by=0,nof.guest_name,u.username) separator '<sep>') AS from_users")
			  ->leftJoin ("#__users as u ON nof.created_by=u.id")

			  ->select ("cm.comment")
			  ->innerJoin ("#__jlexcomment AS cm ON nof.comment_id=cm.id")
			  ->where ($whereClauses)
			  ->group ( "CONCAT(nof.obj_id,'_',nof.comment_id,'_',nof.action_type)" )
			  ->order ("created_time DESC");

		if ( $request->getInt('timestamp') > 0 )
		{
			$timestamp = JFactory::getDate( $request->getInt('timestamp') )->toSql();
			$query->where ('nof.created_time>=' . $this->getDbo()->quote($timestamp));
		}

		if ( $request->getBool ('all', false) == true )
		{
			$notifications = $this->getDbo()->setQuery ($query, $result->offset, $result->limit)->loadObjectList ();
		} else {
			$query->where ("nof.unread=1");
			$notifications = $this->getDbo()->setQuery ($query, 0, 30)->loadObjectList ();
		}
		$total = $this->getDbo()
                        ->setQuery("SELECT FOUND_ROWS()")
                        ->loadResult();

		
		$result->total = $total;
		$result->notifications = $notifications;
		$result->timestamp = JFactory::getDate()->toUnix() + 1;

		if (! $notifications)
		{
			return $result;
		}

		foreach ($result->notifications as $key=>&$nof)
		{
			// {u-264,user}
			if ( !preg_match ("/^\s*$/", $nof->comment) )
			{
				$nof->comment = preg_replace ("/\{u\-[1-9][0-9]*,(.*?)\}/", "<b>$1</b>", $nof->comment);
				$nof->comment = JLexCommentHelper::subwords ( $nof->comment );
			} else {
				$nof->comment = null;
			}

			$nof->object_url = preg_replace("/\#.*/", "", $nof->object_url); // remove hash
			$nof->object_url = JUri::root().ltrim($nof->object_url, "/");

			if($config->get("cm_link",0)==1)
            {
                $up = CommentHelperAdmin::getObjDetail($nof->com_name, $nof->com_key, $nof->object_name, $nof->object_url);
                
                $nof->object_url 	= str_replace(JUri::root(true), "", $up->url);
                $nof->obj_title 	= $up->title;
            }

            $nof->object_url.= (stripos($nof->object_url, '?') !== false ? '&' : '?' ) . 'comment_id=' . $nof->comment_id;

			$nof->url = JRoute::_('index.php?option=com_jlexcomment&task=user.seen&id='.$nof->id ,false);
			$nof->url.= (stripos($nof->url, '?')!==false ? '&' : '?') . 'url=' . urlencode ($nof->object_url);

			$users = explode ("<sep>", $nof->from_users);
			if ( count($users) )
			{
				$preview = array ();
				$i = 0;
				foreach ( $users as $user )
				{
					if ( preg_match("/^\s*$/", $user))
					{
						continue;
					}

					$preview[] = $user;
					$i++;

					if ( $i >= 3 )
					{
						break;
					}
				}
			}

			if ( $nof->count_ppl > 3)
			{
				$prefix = '<b>'.implode("</b>,<b>", $preview) . '</b> '. JText::_("JCM_AND") .' ' .($nof->count_ppl-3). ' ' . JText::_("JCM_PEOPLES");
			} else {
				$prefix = '<b>'.implode("</b>,<b>", $preview) . '</b>';
			}

			switch ( $nof->action_type)
			{
				case 'MENTION':
					$nof->caption = JText::sprintf("JCM_NOF_REMIND", $prefix);
					break;

				case 'COMMENT':
					$nof->caption = JText::sprintf("JCM_NOF_COMMENT", $prefix);
					break;

				default:
					$nof->caption = JText::sprintf("JCM_NOF_REPLY", $prefix);
					break;
			}

			unset ($nof->from_users);
			unset ($nof->count_ppl);
			unset ($nof->action_type);
			unset ($nof->user_remind);
			unset ($nof->comment_id);
			unset ($nof->obj_id);
			unset ($nof->object_url);
		}

		return $result;
	}

	public $id = 0;

	public function seen ()
	{
		if ( $this->id < 1 )
		{
			$this->setError ( JText::_("JCM_PAGE_NOT_FOUND") );
			return false;
		}

		$app = JFactory::getApplication ();
		$user = JFactory::getUser ();

		$query = $this->getDbo()->getQuery (true);
		$query->select ('*')
			  ->from ('#__jlexcomment_notification')
			  ->where ('id=' . $this->id);

		// for mail
		$hash = $app->input->getString ("hash", "");
		if (! empty($hash))
		{
			$rep_email = $app->input->getString ("email", "");
			$hashCompare = md5($this->id . '--' . $rep_email);

			if ($hash!=$hashCompare)
			{
				$this->setError ( JText::_("JCM_PAGE_NOT_FOUND") );
				return false;
			}
		} else {
			$query->where ('user_remind=' . $user->id);
		}

		$return = $this->getDbo()->setQuery ($query)->loadObject ();
		if (! $return)
		{
			$this->setError ( JText::_("JCM_PAGE_NOT_FOUND") );
			return false;
		}

		$whereClauses = array (
				'user_remind=' . $return->user_remind,
				'obj_id=' . $return->obj_id,
				'comment_id=' . $return->comment_id,
				'action_type=' . $this->getDbo()->quote ($return->action_type)
			);
		$subQuery = "UPDATE #__jlexcomment_notification SET unread=0 WHERE " . implode(" AND ", $whereClauses);
		$this->getDbo()->setQuery ($subQuery)->execute();

		$url = urldecode ($app->input->getString('url',''));
		$app->redirect ( $url );
	}

	public function mark_read ()
	{
		$user = JFactory::getUser ();
		$query = "UPDATE #__jlexcomment_notification SET unread=0 WHERE user_remind=" . $user->id;
		$this->getDbo()->setQuery ($query)->execute();

		return true;
	}

	public function offmail ()
	{
		$app = JFactory::getApplication ();
		$uid 	= $app->input->getInt ('uid', 0);
		$email 	= $app->input->getString ('email', 0);
		$hash  	= $app->input->getString ('hash', 0);

		if (empty ($hash))
		{
			/*if ( $uid < 1 )
			{
				$this->setError (JText::_("JCM_USER_NOT_FOUND"));
				return false;
			}*/

			if ($uid>0)
			{
				$user 	= JFactory::getUser ($uid);
				if ($user->email != $email)
				{
					$this->setError (JText::_("JCM_USER_NOT_FOUND"));
					return false;
				}
			}

			$hashCompare = md5 ($uid . '--' . $email);
			if ($hashCompare!=$hash)
			{
				$this->setError (JText::_("JCM_USER_NOT_FOUND"));
				return false;
			}
		} else {
			$user = JFactory::getUser ();
			if ($user->guest)
			{
				$this->setError (JText::_("JCM_YOU_MUST_LOGIN_TO_CONTINUE"));
				return false;
			}

			$uid  = $user->id;
		}

		$query = "SELECT * FROM #__jlexcomment_notification_off WHERE ";
			if($uid>0)
			{
				$query.= "uid=" . $uid;
			} else {
				$query.= "email=" . $this->_db->quote($email);
			}
		$result = $this->_db->setQuery ($query)->loadObject ();

		if ($result)
		{
			$query = "UPDATE #__jlexcomment_notification_off SET published=1 WHERE ";
			if($uid>0)
			{
				$query.= "uid=" . $uid;
			} else {
				$query.= "email=" . $this->_db->quote($email);
			}
		} else {
			$now = JFactory::getDate()->toSql ();
			$query = "INSERT INTO #__jlexcomment_notification_off VALUES (";
				$query .= "{$uid},". $this->_db->quote($email) .",'{$now}',1)";
		}

		$this->_db->setQuery ($query)->execute();
		return true;
	}

	public function onmail ()
	{
		$user = JFactory::getUser ();
		if ($user->guest)
		{
			$this->setError (JText::_("JCM_YOU_MUST_LOGIN_TO_CONTINUE"));
			return false;
		}

		$query = "UPDATE #__jlexcomment_notification_off SET published=0 WHERE uid=" . $uid;
		$this->_db->setQuery ($query)->execute();
		return true;
	}

	/* Using for list_comment */

	public $uid 	= 0;

	public $offset 	= 0;

	public $total 	= 0;
	
	public function list_comment ()
	{
		$app = JFactory::getApplication ();
		$config = JLexCommentHelper::getConfig();
		$request 	= $app->input;
		$limit 		= 10;
		$response 	= new stdClass ();
		$response->limit = $limit;

		/*$this->offset = $request->getInt ('limitstart', 0);*/

		$user = JFactory::getUser ();
		$whereClauses = array (
				'cm.created_by=' . $this->uid,
				'obj.published=1'
			);

		if ($user->id != $this->uid)
		{
			$whereClauses[] = 'cm.published=1';
		}

		$query = $this->getDbo()->getQuery (true);
		$query->select ('SQL_CALC_FOUND_ROWS cm.*, obj.title AS object_name, obj.url AS object_url, obj.com_name, obj.com_key')
			  ->from ("#__jlexcomment cm")
			  ->innerJoin ("#__jlexcomment_obj obj ON cm.obj_id=obj.id");

		// sticker table
        $query->select ('sticker.path2file sk_path2file')
              ->leftJoin ('#__jlexcomment_sticker AS sticker ON(sticker.id=cm.sticker_id AND sticker.published=1) ');
        
        $query->where ($whereClauses)
        	  ->order ('cm.created_time DESC');

        $response->comments = $this->getDbo()->setQuery($query, $this->offset, $limit)->loadObjectList ();
        $this->total = $this->getDbo()
                                    ->setQuery("SELECT FOUND_ROWS()")
                                    ->loadResult();

    	$response->total = $this->total;

        if ( !$response->comments)
        {
        	return $response;
        }

        foreach ($response->comments as $k=>&$comment)
        {
        	if($config->get("cm_link",0)==1)
            {
                $up = CommentHelperAdmin::getObjDetail($comment->com_name, $comment->com_key, $comment->object_name, $comment->object_url);
                $comment->object_url = $up->url;
                $comment->object_url = str_replace(JUri::root(true), "", $comment->object_url);
                $comment->object_name = $up->title;
            }

        	$comment->object_url = JUri::base (true) . '/' . ltrim($comment->object_url,"/") .(stripos($comment->object_url,'?')!==false?'&':'?'). 'comment_id=' . $comment->id;

        	// date
        	$format_date = $config->def("jcm_date_format","sub")=="sub" ? null : $config->get("jcm_date_format");
        	$comment->created_time = JLexCommentHelper::formatTime ($comment->created_time, null, $format_date, ($config->def("jcm_date_tz",1)==1?true:false));

        	// mention
	        $comment->comment = preg_replace_callback('/\{u-([1-9][0-9]*),(.*?)\}/', function($matches){
	            return '<span class="jcm-mention-html" data-id="'.$matches[1].'">'.$matches[2].'</span>';
	        }, $comment->comment);

        	if ($comment->sk_path2file!=null)
	        {
	            $comment->sticker = JUri::root(true) . "/" . $comment->sk_path2file;
	            $comment->comment .= "
	                <div class=\"jcm_sticker\">
	                    <img src=\"{$comment->sticker}\" />
	                    <span class=\"jcm_sticker_holder\"></span>
	                </div>
	            ";
	        }
        }

        return $response;
	}

	public function getUser ()
	{
		$query = $this->getDbo()->getQuery (true);
		$query->select ('u.name,u.username')
			  ->from ('#__users AS u')
			  ->where ('u.id='. $this->uid);

		$info = $this->getDbo()->setQuery ($query)->loadObject ();

		if (! $info)
		{
			throw new Exception(JText::_("JCM_PAGE_NOT_FOUND"), 404);
			return false;
		}

		$info->profile = JLexCommentHelper::getProfile ()->getUser ($this->uid);

		return $info;
	}

	public function login ()
	{
		$config = JLexCommentHelper::getConfig();
		$app = JFactory::getApplication();
		$post = $app->input->post;
		
		if ( preg_match ("/^\s*$/", $post->getUsername ( 'username' )) || $post->getString ( 'password' ) == '')
		{
			$this->setError ( JText::_("JCM_FILL_USERNAME_PW_FIELD") );
			return false;
		}
		
		$credentials ['username'] = $post->getUsername ( 'username' );
		$credentials ['password'] = $post->getString ( 'password' );
		
		$option = array (
				'remember' => $config->get('remember_me',0)==1
		);
		
		$result = $app->login( $credentials, $option );
		
		if (! $result)
		{
			$this->setError( JText::_("JCM_USERNAME_OR_PW_INCORRECT") );
			return false;
		}

		return true;
	}

	public function oauth()
	{
		$app 		= JFactory::getApplication();
		$session 	= JFactory::getSession();
		$config 	= JLexCommentHelper::getConfig();
		$case 		= $app->input->getCmd('type','fb');
		$ssl 		= JUri::getInstance()->isSSL();
		$error_url 	= JUri::root(true) . '/index.php?option=com_jlexcomment&task=user.oauth_issue';
		
		$jUser 		= JFactory::getUser();
		if ($jUser->id>0) return false; // user is joined

		$data 	= array (); // basic info
		$jUser 	= JFactory::getUser(0); // reset
		$extraProfile = array('auth'=>$case); // advanced info

		if ($case=='fb')
		{
			if (!$config->get('oauth_fb',0)||$config->def('oauth_fb_public','')==''||$config->get('oauth_fb_secret','')=='')
			{
				$this->setError('You must set APP_ID and APP_SECRET for this app.');
				return false;
			}

			require_once dirname(__FILE__).'/oauth/Facebook/autoload.php';

			$access 	= $app->input->getBool("access", false);
			$redirect 	= JRoute::_('index.php?option=com_jlexcomment&task=user.oauth&type=fb&access=1',false,($ssl?1:2));

			$fb = new Facebook\Facebook(array(
				  'app_id' 		=> $config->get('oauth_fb_public',''),
				  'app_secret' 	=> $config->get('oauth_fb_secret',''),
				  'default_graph_version' => 'v2.5',
				));

			$helper = $fb->getRedirectLoginHelper();

			if ($access)
			{
				$access_denied = $app->input->getCmd('error','')=='access_denied';
				if($access_denied)
				{
					// user not share permission
					$error_msg = $app->input->getString('error_description','');
					$url_issue = JRoute::_('index.php?option=com_jlexcomment&task=user.oauth_issue&issue_msg='.$error_msg,false);
					$app->redirect();
					return;
				}

				if(!array_key_exists('code',$_GET))
				{
					$_GET['code'] 	= $app->input->get('code','');
					$_GET['state'] 	= $app->input->get('state','');
				} 
  
				try {  
					$accessToken = $helper->getAccessToken($redirect);  
				} catch(Facebook\Exceptions\FacebookResponseException $e) {  
					// When Graph returns an error  
					$this->setError('Graph returned an error: ' . $e->getMessage());  
					return false; 
				} catch(Facebook\Exceptions\FacebookSDKException $e) {  
					// When validation fails or other local issues  
					$this->setError('Facebook SDK returned an error: ' . $e->getMessage());
					return false;
				}  


				try
				{
					$response = $fb->get('/me?fields=id,name,email', $accessToken->getValue());
					
				} catch(Facebook\Exceptions\FacebookResponseException $e) {
					// When Graph returns an error
					$this->setError('ERROR: Graph ' . $e->getMessage());
					return false;
				} catch(Facebook\Exceptions\FacebookSDKException $e) {
					// When validation fails or other local issues
					$this->setError('ERROR: validation fails ' . $e->getMessage());
					return false;
				}

				$me = $response->getGraphUser();

				$data['name'] 		= $me->getProperty('name');
				$data['username'] 	= $me->getProperty('name');
				$data['email'] 		= $me->getProperty('email');
				$extraProfile['auth_id'] 		= $me->getProperty('id');
				$extraProfile['auth_url'] 		= 'https://www.facebook.com/' . $me->getProperty('id');
				$extraProfile['auth_picture'] = 'https://graph.facebook.com/' . $me->getProperty('id') . '/picture?width=200&height=200';
			} else {
				// login
				$permissions 	= array("email");
				$loginUrl 		= $helper->getLoginUrl($redirect, $permissions);
				
				$app->redirect( $loginUrl . '&display=popup');
				return;
			}
		} elseif ($case == 'google') {
			$client_id 		= $config->get('oauth_gg_public','');
			$client_secret 	= $config->get('oauth_gg_secret','');
			$redirect 		= urlencode( JURI::root() . 'index.php?option=com_jlexcomment&task=user.oauth&type=google');
			
			if (!$config->get( 'oauth_gg',0)||trim($client_id)==''||trim($client_secret)=='')
			{
				$this->setError("You must set APP_ID and APP_SECRET to use this feature.");
				return false;
			}

			$code = $app->input->getString('code','');
			if (empty($code))
			{
				$scope = urlencode('https://www.googleapis.com/auth/userinfo.profile https://www.googleapis.com/auth/userinfo.email' );
				
				$params = array (
						'response_type=code',
						'redirect_uri=' . $redirect,
						'client_id=' . $client_id,
						'scope=' . $scope 
				);
				
				$params = implode('&', $params);
				$url 	= 'https://accounts.google.com/o/oauth2/auth?' . $params;
				$app->redirect($url);
				$app->close();
			}
			
			// Checking token
			$scope = urlencode('https://www.googleapis.com/auth/userinfo.profile https://www.googleapis.com/auth/userinfo.email');
			
			$params = array (
					'client_id=' . $client_id,
					'client_secret=' . $client_secret,
					'grant_type=authorization_code',
					'code=' . $code,
					'redirect_uri=' . $redirect,
					'scope=' . $scope 
			);
			$params = implode('&',$params);
			$url 	= 'https://accounts.google.com/o/oauth2/token';
			
			// request URL to get access token
			$request = json_decode(JLexCommentHelper::getUrl($url,true,$params));
			
			if (empty($request))
			{
				$this->setError(JText::sprintf('JCM_COULD_NOT_GET_RESPONSE_FROM_SERVICE', 'Google'));
				return false;
			}
			
			$url = 'https://www.googleapis.com/oauth2/v1/userinfo?access_token=' . $request->access_token;
			$request = json_decode(JLexCommentHelper::getUrl( $url ));
			
			if (empty($request))
			{
				$this->setError(JText::sprintf('JCM_COULD_NOT_GET_RESPONSE_FROM_SERVICE', 'Google'));
				return false;
			}

			if (!empty($request->error)){
				$this->setError(JText::sprintf('JCM_ERROR_PRINT', $request->error));
				return false;
			}
			
			// assign profile data
			$data ['name'] = $request->given_name . ' ' . $request->family_name;
			$data ['username'] = $request->given_name . '_' . $request->family_name;
			$data ['email'] = $request->email;
			/*
			 * ID: $request->id Avatar:
			 * $request->picture?$request->picture:false;
			 */
			$extraProfile ['auth_id'] = $request->id;
			$extraProfile ['auth_url'] = '';
			$extraProfile ['auth_picture'] = isset ( $request->picture ) ? $request->picture : '';
		} elseif ($case == 'twitter') {
			$CONSUMER_KEY 		= $config->get('oauth_tw_public','');
			$CONSUMER_SECRET 	= $config->get('oauth_tw_secret','');
			$OAUTH_CALLBACK 	= JUri::root().'index.php?option=com_jlexcomment&task=user.oauth&type=twitter';
			
			if (!$config->get('oauth_tw',0)||trim($CONSUMER_KEY)==''||trim($CONSUMER_SECRET)=='')
			{
				$this->setError("You must set Twitter's params to use this feature.");
				return false;
			}

			require_once dirname(__FILE__).'/oauth/twitteroauth/twitteroauth.php';
			
			$oauth_token 	= $app->input->getString('oauth_token', 0);
			$oauth_verifier = $app->input->getString('oauth_verifier', 0);
			
			if (empty($oauth_token)||empty($oauth_verifier))
			{
				$connection 	= new TwitterOAuth($CONSUMER_KEY,$CONSUMER_SECRET);
				$request_token 	= $connection->getRequestToken($OAUTH_CALLBACK);
				if ($request_token)
				{
					$token = $request_token['oauth_token'];
					$session->set('tw_request_token', $token );
					$session->set('tw_request_token_secret',$request_token['oauth_token_secret']);
					if ($connection->http_code==200)
					{
						$url = $connection->getAuthorizeURL($token);
						$app->redirect($url);
						$app->close();
					}
				}
				$this->setError(JText::sprintf('JCM_COULD_NOT_GET_RESPONSE_FROM_SERVICE', 'Twitter'));
				return false;
			}
			
			$connection 	= new TwitterOAuth($CONSUMER_KEY,$CONSUMER_SECRET,$session->get('tw_request_token'), $session->get('tw_request_token_secret'));
			$access_token 	= $connection->getAccessToken ($oauth_verifier);
			
			if (!$access_token)
			{
				$this->setError(JText::sprintf('JCM_COULD_NOT_GET_RESPONSE_FROM_SERVICE', 'Twitter'));
				return false;
			}
			$connection = new TwitterOAuth($CONSUMER_KEY, $CONSUMER_SECRET, $access_token['oauth_token'], $access_token['oauth_token_secret']);
			$params = ['include_entities'=>false, 'include_email' => 'true'];
			$content = $connection->get('account/verify_credentials', $params);
			
			if ($content && isset($content->screen_name) && isset($content->name))
			{
				$data['name'] = $content->name;
				$data['username'] = $content->screen_name;
				$data['email'] = isset($content->email) ? $content->email : ('tw_' . $content->id . '@twitter.com');
				$extraProfile['auth_id'] = $content->id;
				$extraProfile['auth_url'] = 'https://twitter.com/' . $content->screen_name;
				$extraProfile['auth_picture'] = str_replace("http://", "https://", $content->profile_image_url);
			} else {
				$this->setError(JText::sprintf('JCM_COULD_NOT_GET_RESPONSE_FROM_SERVICE', 'Twitter'));
				return false;
			}
		} elseif ($case=='vk') {
			$client_id		= $config->get('oauth_vk_app_id');
			$client_secret	= $config->get('oauth_vk_app_secret');
			$scope 			= 'email';
			$version 		= '5.81';
			$redirect_url	= JUri::root().'index.php?option=com_jlexcomment&task=user.oauth&type=vk&callback=1';
			$request_url 	= 'https://oauth.vk.com/authorize?client_id='.$client_id.'&display=popup&redirect_uri='.urlencode($redirect_url).'&scope='.$scope.'&response_type=code&v='.$version;

			if($client_id=='' || $client_secret=='')
			{
				$this->setError(JText::_('JCM_VK_EMPTY_ALERT'));
				return false;
			}

			$callback 		= $app->input->getBool('callback',false);
			if(!$callback)
			{
				// authentication require.
				$app->redirect($request_url);
				return;
			}

			$code 	= $app->input->getString('code','');
			$error 	= $app->input->getCmd('error','');
			$msg 	= $app->input->getString('error_description','');

			if(preg_match('/^\s*$/',$code) || $error!='')
			{
				// return to error page.
				$this->setError(JText::sprintf('JCM_ERROR_PRINT', $msg));
				return;
			}

			// get email & access_token
			$access_request_url = 'https://oauth.vk.com/access_token?client_id='.$client_id.'&client_secret='.$client_secret.'&redirect_uri='.urlencode($redirect_url).'&code='.$code;

			// run request
			$response = json_decode(JLexCommentHelper::getUrl($access_request_url));

			if(!$response || !is_object($response))
			{
				$this->setError(JText::sprintf('JCM_COULD_NOT_GET_RESPONSE_FROM_SERVICE', 'VKontakte'));
				return;
			}

			if(isset($response->error))
			{
				// $response->error
				// $response->error_description
				$this->setError(JText::sprintf('JCM_ERROR_PRINT', $response->error_description));
				return;
			}

			// @since v3.2.9
			$data['email']=isset($response->email)?$response->email:$response->user_id.'@vk.com';

			// get basic information
			$basic_info_url = 'https://api.vk.com/method/getProfiles?uid='.$response->user_id.'&access_token='.$response->access_token.'&fields=photo_200,screen_name&v='.$version;

			// run request
			$response = json_decode(JLexCommentHelper::getUrl($basic_info_url));

			if(!$response || !is_object($response))
			{
				$this->setError(JText::sprintf('JCM_COULD_NOT_GET_RESPONSE_FROM_SERVICE', 'VKontakte'));
				return;
			}

			if(isset($response->error))
			{
				// $response->error
				// $response->error->error_msg
				$this->setError(JText::sprintf('JCM_ERROR_PRINT', $response->error_description));
				return;
			}

			// finally :)
			$user_info 						= $response->response[0];
			$data['name']					= $user_info->first_name.' '.$user_info->last_name;
			$data['username']				= $user_info->screen_name;
			$extraProfile['auth_id'] 		= $user_info->id;
			$extraProfile['auth_picture']	= $user_info->photo_200;

			/** END VK **/
		} else {
			throw new Exception(JText::_("JCM_PAGE_NOT_FOUND"), 404);
		}
		
		$data ['usertype'] = 'deprecated';
		$data ['groups'] = array (
				2 
			);
		$data ['registerDate'] = JFactory::getDate ()->toSQL ();
		$data ['password'] = JUserHelper::genRandomPassword ();
		$data ['password2'] = $data ['password'];
		$data ['sendEmail'] = 0;
		$data ['block'] = 0;
		
		// check user exist
		$userByEmail = $this->getUserByEmail ( $data ['email'] );
		if ($userByEmail) {
			// change password
			$randPassword = $this->randomkeys ();
			$newPassword = JUserHelper::hashPassword ( $randPassword );
			$this->getDbo ()->setQuery ( "UPDATE #__users SET password=" . $this->getDbo ()->quote ( $newPassword ) . " WHERE id=" . $userByEmail->id )->execute();
			
			// try login
			$credentials = array ();
			$credentials ['username'] = $userByEmail->username;
			$credentials ['password'] = $randPassword;

			$login_option = array (
				'remember' => $config->get('remember_me',0)==1
			);

			$result = $app->login ( $credentials, $login_option );
			
			// re-change password
			$this->getDbo ()->setQuery ( "UPDATE #__users SET password=" . $this->getDbo ()->quote ( $userByEmail->password ) . " WHERE id=" . $userByEmail->id )->execute();
			if ($result) {
				return true;
			}
			$this->setError ( JText::_("JCM_THE_ACCOUNT_BLOCKED") );
			return false;
		}
		
		// Safe username
		$username = ( string ) preg_replace ( '/[^A-z0-9_]/i', '', strtolower ( $data ['username'] ) );
		if (strlen ( $username ) < 2) {
			$username .= '_user';
		}

		// username must be unique
		if ($this->checkUsername ( $username )) {
			$i = 1;
			$user_login_tmp = $username;
			do {
				$user_login_tmp = $username . ($i ++);
			} while ( $this->checkUsername ( $user_login_tmp ) );
			
			// unique user login
			$username = $user_login_tmp;
		}

		$data ['username'] = $username;
		
		// Register a new user
		if (! $jUser->bind ( $data )) {
			$msg = JText::_ ( 'Could not bind data to user' ) . ': ' . JText::_ ( $jUser->getError () );
			$this->setError ( $msg );
			return false;
		}
		
		// save the user
		if (! $jUser->save ()) {
			$msg = JText::_ ( 'Could not create user' ) . ': ' . JText::_ ( $jUser->getError () );
			$this->setError ( $msg );
			return false;
		}
		
		// assign to JLex User table
		$extraProfile ['userid'] = $jUser->id;
		$extraProfile ['created'] = JFactory::getDate ()->toSql ();
		$jlexUser = $this->getTable ( 'User', 'TableCm' );
		$jlexUser->bind ( $extraProfile );
		$jlexUser->store ();
		
		// Login
		$credentials = array ();
		$credentials ['username'] = $data ['username'];
		$credentials ['password'] = $data ['password2'];
		$login_option = array (
				'remember' => $config->get('remember_me',0)==1
		);
		
		$result = $app->login ( $credentials, $login_option );
		if (! $result) {
			$this->setError ( JText::_("JCM_COULD_NOT_LOGIN") );
			return false;
		}
		return true;
	}

	
	/**
	 * Check username is available
	 *
	 * @param string $username        	
	 * @return boolean
	 */
	protected function checkUsername ($username)
	{
		$query = "SELECT id FROM #__users WHERE username = " . $this->_db->quote ( $username );
		$uid   = $this->_db->setQuery ($query)->loadResult ();
		
		// done
		return ! $uid ? false : $uid*1;
	}
	
	/**
	 * Check username is available base on email
	 *
	 * @param string $username        	
	 * @return object | boolean
	 */
	protected function getUserByEmail ($email)
	{
		// get user for username
		$query = "SELECT id,username,password FROM #__users WHERE email = " . $this->_db->quote ( $email );
		$user  = $this->_db->setQuery ($query, 0 ,1)->loadObject ();
		
		// done
		return $user ? $user : false;
	}
	/**
	 * Get random key use for pw or anything
	 *
	 * @param int $length        	
	 * @return string
	 */
	protected function randomkeys ($length = 5)
	{
		$pattern = "ABCDEFGHIJKLMNOPQRSTUVWXYZ123456789abcdefghijklmnopqrstuvwxyz";
		$key = '';
		for($i = 0; $i < $length; $i ++) {
			$key .= $pattern[rand(0, strlen($pattern)-1)];
		}
		return $key;
	}
}