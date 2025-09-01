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

class JLexCommentModelSubscribe extends JModelLegacy
{
	protected $id = 0;

	public function findObject()
	{
		$key=JFactory::getApplication()->input->getString('key', '');
		$parse=JLexCommentHelper::decodeSub($key);

		if(!$parse)
		{
			$this->setError("Key incorrect.");
			return false;
		}

		$query = $this->_db->getQuery(true);
		$query->select("id")
			  ->from("#__jlexcomment_obj")
			  ->where([
			  		"com_name=".$this->_db->quote($parse->com_name),
			  		"com_key=".$this->_db->quote($parse->com_key),
			  		"published=1"
			  	]);

		$id = $this->_db->setQuery($query)->loadResult();

		if(!$id)
		{
			$this->setError(JText::_("JCM_ENTRY_NOT_FOUND"));
			return false;
		}

		return $id;
	}

	public function suggest()
	{
		$this->id = $this->findObject();

		if(!$this->id) return false;

		$response = array (
			'name' => '',
			'email' => '',
			'followed' => 0
		);

		$user = JFactory::getUser();
		if($user->guest) return $response;

		$query = $this->_db->getQuery(true);
		$query->select("COUNT(*)")
			  ->from("#__jlexcomment_subscribe")
			  ->where([
			  		"obj_id=".$this->_db->quote($this->id),
			  		"created_by=".$this->_db->quote($user->id),
			  		"published=1"
			  	]);

		$result = $this->_db->setQuery($query)->loadResult();

		if($result>0)
			$response['followed'] = 1;

		$response['name'] = $user->name;
		$response['email'] = $user->email;

		return $response;
	}

	public function add($data=null)
	{
		$app 		= JFactory::getApplication();
		$request 	= $app->input;
		$user 		= JFactory::getUser();
		$config 	= JLexCommentHelper::getConfig();
		$path   	= trim($config->get('email_tpl_path',''),'/');
		$configSys 	= JFactory::getConfig();
		$date 		= JFactory::getDate();
        
        if(preg_match('/^\s*$/', $path))
        {
            $path = 'administrator/components/com_jlexcomment/libraries/email_templates';
        }

        $path = JPATH_ROOT.'/'.$path;
		if($config->get("u_sub_comment",false)==false)
		{
			$this->setError(JText::_("JCM_THIS_FEATURE_IS_DISABLED"));
			return false;
		}

		$obj_id = $this->findObject();
		if(!$obj_id)
		{
			$this->setError("Key incorrect.");
			return false;
		}

		$row = $this->getTable('subscribe', 'TableCm');
		$row->bind(($data==NULL?$request->getArray(["name"=>"string", "email"=>"string"]):$data), array('id','email_confirmed','point'));

		$row->set('obj_id', $obj_id);
		$row->set('created_by', $user->id);
		$row->set('created_time', $date->toSql());

		if($config->get('email_verify', 0)==0)
		{
			$row->set('email_confirmed',1);
		}

		if(!$row->check())
		{
			$row->load( $row->getId() );
			
			if($row->published==1)
			{
				$this->setError($row->getError());
				return false;
			}
		}

		$row->set('point', $date->toUnix());
		$row->set('published', 1);

		if(!$row->store())
		{
			$this->setError(JText::_("JCM_APPEAR_ERROR_WHEN_SAVING_YOUR_DATE_TRY_LATER"));
			return false;
		}

		// confirm email
		if($config->get("email_verify",0)==1 && !$row->email_confirmed && $user->email!=$row->email)
		{
			$query = $this->_db->getQuery(true);
			$query->select("*")
				  ->from("#__jlexcomment_obj")
				  ->where("id=".$this->_db->quote($obj_id));

			$entry = $this->_db->setQuery($query)->loadObject ();

			$email_tpl = $path . "/subscriber_verify.php";
			if(!JFile::exists($email_tpl) || !$entry)
			{
				// tpl not found.
				return false;
			}

			$entry->guest_name = $row->name;
			$entry->link2verify = JRoute::_("index.php?option=com_jlexcomment&task=subscribe.activation&hash=". md5($row->email_hash) ."&id=" . $row->id, false, ($configSys->get('force_ssl',0)==2?1:-1));

			$title = JText::sprintf("JCM_EMAIL_CONFIRM_TO_FOLLOW",$entry->title);

			// bind to template
        	ob_start();
        	include $email_tpl;
        	$body = ob_get_contents();
        	ob_end_clean();

        	// send.
        	$mailer = JFactory::getMailer();
        	$mailer->isHTML( true );
			$mailer->setSubject( $title );
			$mailer->addRecipient( $row->email, $row->name );
			$mailer->setBody( $body );
			$mailer->Send();
		}

		return $user->guest?$row->email_hash:1;
	}

	public function activation()
	{
		$app = JFactory::getApplication();
		$hash_email = $app->input->getString("hash", "");
		$sub_id = $app->input->getInt ("id", 0);

		if($sub_id<1)
		{
			throw new Exception(JText::_("JCM_PAGE_NOT_FOUND"), 404);
			return false;
		}

		$query = $this->_db->getQuery(true);
		$query->select("*")
			  ->from("#__jlexcomment_subscribe")
			  ->where("id=".$this->_db->quote($sub_id));
		
		$row = $this->_db->setQuery($query)->loadObject();

		if(!$row)
		{
			throw new Exception(JText::_("JCM_PAGE_NOT_FOUND"), 404);
			return false;
		}

		if(md5($row->email_hash)!=$hash_email)
		{
			$this->setError(JText::_("JCM_ACTIVATION_FAILURE"));
			return false;
		}

		$date = JFactory::getDate();
		$query->clear()
			  ->update("#__jlexcomment_subscribe")
			  ->set([
			  		"email_confirmed=1",
			  		"point=".$this->_db->quote($date->toUnix())
			  	])
			  ->where("id=".$this->_db->quote($sub_id));

		$this->_db->setQuery($query)->execute();

		$obj = JLexCommentHelper::getObjDetail($row->obj_id);
		return $obj->url;
	}

	public function remove()
	{
		$app 		= JFactory::getApplication();
		$user 		= JFactory::getUser();
		$request 	= $app->input;

		$obj_id 	= $this->findObject();
		$hash		= $request->getString('hash','');

		if(!$obj_id)
		{
			$this->setError("Key incorrect.");
			return false;
		}

		$query = $this->_db->getQuery(true);
		if(!preg_match('/^\s*$/', $hash))
		{
			// delete("#__jlexcomment_subscribe")
			$query->update("#__jlexcomment_subscribe")
				  ->set('published=0')
				  ->where([
				  		"obj_id=".$this->_db->quote($obj_id),
				  		"email_hash=".$this->_db->quote($hash)
				  	]);
		} elseif($user->id>0) {
			// delete("#__jlexcomment_subscribe")
			$query->update("#__jlexcomment_subscribe")
				  ->set('published=0')
				  ->where([
				  		"obj_id=".$this->_db->quote($obj_id),
				  		"created_by=".$this->_db->quote($user->id)
				  	]);
		} else {
			$this->setError(JText::_("JCM_ROW_NOT_FOUND"));
			return false;
		}

		$this->_db->setQuery($query)->execute();
		$return = $this->_db->getAffectedRows();

		if(!$return)
		{
			$this->setError(JText::_("JCM_ROW_NOT_FOUND"));
			return false;
		}

		return true;
	}

	public function mail($uid=0)
	{
		set_time_limit(0);
		$person_each_time = 20;
		$config = JLexCommentHelper::getConfig();
		$date = JFactory::getDate();
		$configSys = JFactory::getConfig();
		$path = trim($config->get('email_tpl_path',''),'/');

        if(preg_match('/^\s*$/', $path))
        {
            $path = 'administrator/components/com_jlexcomment/libraries/email_templates';
        }

        $path = JPATH_ROOT.'/'.$path;

        // begin query
		$query = $this->_db->getQuery(true);
		$query->select('s.*, GROUP_CONCAT(s.obj_id) AS cid')
			  ->from('#__jlexcomment_subscribe s')
			  ->leftJoin('#__jlexcomment_obj o ON (s.obj_id=o.id AND s.point<o.latest_update)')
			  ->where([
			  	'o.published=1',
			  	's.published=1'
			  ])
			  ->order('s.point ASC')
			  ->group('s.email');

		if($uid>0){
			$query->where('s.created_by='.$this->_db->quote($uid))
				  ->group('s.created_by');

		} elseif($config->get('email_verify',0)==1){
			$query->where('s.email_confirmed=1');
		}

		$users = $this->_db->setQuery($query, 0, $person_each_time)->loadObjectList();

		// Bind to template
	   	$email_tpl = $path . "/subscription.php";
	    if(!is_file($email_tpl) && !$uid) return false;

	    if(!$uid && $config->get('email_verify',0)==1)
	    {
	    	$query->clear()
	    		  ->update("#__jlexcomment_subscribe")
	    		  ->set("point=".$this->_db->quote($date->toUnix()))
	    		  ->where("email_confirmed=0");

	    	$this->_db->setQuery($query)->execute();
	    }

		if(!$users)
		{
			// no user available, reset point
			$query->clear()
	    		  ->update("#__jlexcomment_subscribe")
	    		  ->set("point=".$this->_db->quote($date->toUnix()));

	    	// for special user
	    	if($uid>0) $query->where('created_by='.$this->_db->quote($uid));

	    	$this->_db->setQuery($query)->execute();
			return null;
		}

		$screen_name = $config->get('author_name','screen_name');

		foreach($users as $user)
		{
			$cid = explode(",", $user->cid);
			$titles = [];

			// get comments
			$query->clear()
				  ->select('SQL_CALC_FOUND_ROWS cm.id, cm.comment, cm.guest_name, u.name author_name, u.username, COUNT(cm.id) AS count_cm, cm.obj_id, cm.created_time, cm.created_by')
				  ->from("#__jlexcomment cm")
				  ->leftJoin("#__users u ON cm.created_by=u.id")
				  ->where([
				  		'cm.published=1',
				  		'cm.created_time>='.$this->_db->quote(JFactory::getDate($user->point)->toSql()),
				  		'cm.created_time<'.$this->_db->quote($date->toSql()),
				  		'cm.parent_id=0',
				  		'cm.obj_id IN('.$user->cid.')'
				  	])
	        	  ->order('cm.up_point DESC')
	        	  ->group('cm.obj_id');

	        // entry table
	        $query->select("o.title, o.url, o.com_name, o.com_key")
	        	  ->leftJoin("#__jlexcomment_obj o ON cm.obj_id=o.id");

	        if(!$user->created_by)
			{
				$query->where('(cm.guest_email="" OR cm.guest_email IS NULL OR cm.guest_email!=' . $this->_db->quote($user->email).')');
			} else {
				$query->where('cm.created_by!='.$user->created_by);
			}

	       	$comments = $this->_db->setQuery($query, 0, 7)->loadObjectList();
	       	$total = (int) $this->_db->setQuery("SELECT FOUND_ROWS()")->loadResult();

	       	// update point
	       	$query->clear()
	       		  ->update("#__jlexcomment_subscribe")
	       		  ->set("point=".$this->_db->quote($date->toUnix()))
	       		  ->where("id=".$this->_db->quote($user->id));

	       	$this->_db->setQuery($query)->execute();

	       	if(!$comments) return null;

	       	$uri = JUri::getInstance();
	        $base = $uri->toString(array(
	            'scheme',
	            'host',
	            'user',
	            'pass'
	        ));

	       	foreach($comments as $k=>$cm)
	       	{
	       		$comments[$k]->author = !empty($cm->username)?($screen_name=='screen_name'?$cm->author_name:$cm->username) : $cm->guest_name;

	       		$comments[$k]->comment = preg_replace_callback('/\{u-([1-9][0-9]*),(.*?)\}/', function($matches){
			            return '<b>@'.$matches[2].'</b>';
			        }, $cm->comment);

	       		$comments[$k]->comment = nl2br(JLexCommentHelper::subwords($cm->comment,20));

	       		if($config->get("cm_link",0)==1)
	            {
	                $router = CommentHelperAdmin::getObjDetail($cm->com_name, $cm->com_key, $cm->title, $cm->url);
	                $comments[$k]->title = $router->title;
	                $comments[$k]->url = $router->url;
	            } else {
	                $comments[$k]->url = JUri::root(true) . "/" . ltrim($cm->url, "/");
	            }

	            $comments[$k]->url = $base.$comments[$k]->url;

	            if($k<=1)
	            	$titles[] = $comments[$k]->title;

	       		// add notification
	       		if($user->created_by>0)
	       		{
	       			$dt = [
			       		"obj_id" => $cm->obj_id,
			       		"comment_id" => $cm->id,
			       		"action_type" => "COMMENT",
			       		"created_time" => $date->toSql(),
			       		"created_by" => $cm->created_by,
			       		"guest_name" => ($cm->created_by>0?"":$cm->guest_name),
			       		"user_remind" => $user->created_by,
			       		"sent" => 1,
			       		"unread" => 1
			       	];

			       	$row = $this->getTable("notification", "TableCm");
			        $row->bind($dt);
			        if($row->check()) $row->store();
			    }

			    unset($comments[$k]->com_name);
	            unset($comments[$k]->com_key);
	       		unset($comments[$k]->guest_name);
	       		unset($comments[$k]->author_name);
	       		unset($comments[$k]->username);
	       	}

	       	// email params
	       	if(!$uid)
	       	{
	       		$title = jtext::sprintf("JCM_NOF_ENTRY_NOTIFICATION", implode(", ", $titles));

		       	$unsubscribe = JRoute::_('index.php?option=com_jlexcomment&task=subscribe.stop&hash=' . $user->email_hash, false, ($configSys->get('force_ssl',0)==2?1:-1));

		       	// send mail
	        	ob_start ();
	        	include $email_tpl;
	        	$body = ob_get_contents();
	        	ob_end_clean();

	        	// send.
	        	$mailer = JFactory::getMailer();
	        	$mailer->isHTML(true);
				$mailer->setSubject($title);
				$mailer->addRecipient($user->email, $user->name);
				$mailer->setBody($body);
				$mailer->Send();
			}
		}

		return true;
	}

	public function mailq ()
	{
		set_time_limit(0);
		$app = JFactory::getApplication ();
		$query = $this->getDbo()->getQuery (true);
		$config = JLexCommentHelper::getConfig();
		$configSys = JFactory::getConfig();
		$uprefix = $config->get('author_name','screen_name')=='screen_name'?'name':'username';

		if($config->get("nof_user",0)==0 || ($config->get("nof_mention_type",1)==0 && $config->get("nof_reply_type",1)==0))
		{
			// this feature is disabled.
			return false;
		}

		$path = trim($config->get('email_tpl_path',''),'/');
        
        if(preg_match('/^\s*$/', $path))
        {
            $path = 'administrator/components/com_jlexcomment/libraries/email_templates';
        }
        $path 	= JPATH_ROOT.'/'.$path;

		$email_tpl = $path . "/mention.php";
	    if (! JFile::exists ($email_tpl))
	    {
	    	return false;
	    }

		$whereClauses = array ();
		$whereClauses[] = "obj.published=1";
		$whereClauses[] = "cm.published=1";
		$whereClauses[] = "nof.sent=0";

		if($config->get("nof_mention_type",1)==0)
		{
			$whereClauses[] = "nof.action_type!=" . $this->_db->quote ("MENTION");
		}

		if($config->get("nof_reply_type",1)==0)
		{
			$whereClauses[] = "nof.action_type!=" . $this->_db->quote ("REPLY");
		}

		$nof_date = $config->get("nof_date",null);
		if(!empty($nof_date))
		{
			$offset_date = JFactory::getDate($config->get("nof_date"))->toSql();
			$whereClauses[] = "nof.created_time>=" . $this->_db->quote($offset_date);
		}

		$query->select("SQL_CALC_FOUND_ROWS nof.*")
			  ->select("COUNT(nof.id) count_ppl")
			  ->select("MAX(nof.created_time) created_time")
			  ->from("#__jlexcomment_notification AS nof")

			  ->select("obj.title AS object_name, obj.url AS object_url")
			  ->innerJoin("#__jlexcomment_obj AS obj ON nof.obj_id=obj.id")

			  ->select("GROUP_CONCAT( IF(nof.created_by=0,nof.guest_name,u.{$uprefix}) separator '<sep>') AS from_users")
			  ->leftJoin("#__users as u ON nof.created_by=u.id")

			  ->select("IF(u1.{$uprefix} IS NULL,nof.guest_remind_name,u1.{$uprefix}) rep_name, IF(u1.email IS NULL,nof.guest_remind_email,u1.email) rep_email")
			  ->leftJoin("#__users as u1 ON nof.user_remind=u1.id")

			  ->select("cm.comment")
			  ->innerJoin("#__jlexcomment AS cm ON nof.comment_id=cm.id")
			  ->where($whereClauses)
			  ->group( "CONCAT(nof.guest_remind_email,'_',nof.user_remind,'_',nof.obj_id,'_',nof.comment_id,'_',nof.action_type)" )
			  ->order("nof.created_time ASC");

		$query->where("nof.unread=1");
		
		$notifications = $this->getDbo()->setQuery($query, 0, 20)->loadObjectList();

		if(!$notifications)
		{
			return null;
		}

		foreach ($notifications as $key=>&$nof)
		{
			// check if this email is unsubscribe
			$subQuery = "SELECT COUNT(*) FROM #__jlexcomment_notification_off";
				if($nof->user_remind>0)
				{
					$subQuery.= "\nWHERE uid={$nof->user_remind} AND published=1";
				} else {
					$subQuery.= "\nWHERE email=". $this->_db->quote($nof->guest_remind_email) ." AND published=1";
				}
				
			$locked   = $this->_db->setQuery ($subQuery)->loadResult () > 0 ? true : false;

			// update "sent" status
			$subQuery = "UPDATE #__jlexcomment_notification SET sent=1 WHERE ";
				$subQuery .= "obj_id=" . $nof->obj_id;
				$subQuery .= " AND comment_id=" . $nof->comment_id;
				$subQuery .= " AND action_type=" . $this->_db->quote ($nof->action_type);
				if($nof->user_remind>0)
				{
					$subQuery .= " AND user_remind=" . $nof->user_remind;
				} else {
					$subQuery .= " AND guest_remind_email=" . $this->_db->quote($nof->guest_remind_email);
				}
				
			$this->_db->setQuery ($subQuery)->execute();

			if ($locked)
			{
				continue;
			}

			// {u-264,user}
			if ( !preg_match ("/^\s*$/", $nof->comment) )
			{
				$nof->comment = preg_replace ("/\{u\-[1-9][0-9]*,(.*?)\}/", "<b>@$1</b>", $nof->comment);
				$nof->comment = JLexCommentHelper::subwords ( $nof->comment, 20 );
			} else {
				$nof->comment = null;
			}

			$nof->object_url = preg_replace("/\#.*/", "", $nof->object_url); // remove hash
			$nof->object_url = JUri::root () . $nof->object_url . ( stripos($nof->object_url, '?') !== false ? '&' : '?' ) . 'comment_id=' . $nof->comment_id;
		
			$hash = md5 ($nof->id . '--' . $nof->rep_email);
			$hashUrl = '&email='.$nof->rep_email.'&hash=' . $hash;

			$nof->url = JRoute::_('index.php?option=com_jlexcomment&task=user.seen&id='.$nof->id . '&'.$hashUrl ,false, ($configSys->get('force_ssl',0)==2?1:-1));
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

				default:
					$nof->caption = JText::sprintf("JCM_NOF_REPLY", $prefix);
					break;
			}

			unset ($nof->from_users);
			unset ($nof->count_ppl);
			unset ($nof->action_type);
			/*unset ($nof->user_remind);*/
			unset ($nof->comment_id);
			unset ($nof->obj_id);
			unset ($nof->object_url);

			$hash = md5 ($nof->user_remind . '--' . $nof->rep_email);
			$hashUrl = 'uid='.$nof->user_remind.'&email='.$nof->rep_email.'&hash=' . $hash;
			$nof->unsubscribe = JRoute::_('index.php?option=com_jlexcomment&task=user.offmail&'.$hashUrl, false, ($configSys->get('force_ssl',0)==2?1:-1));

			// bind & send mail
        	ob_start ();
        	include $email_tpl;
        	$body = ob_get_contents ();
        	ob_end_clean();

        	// send.
        	$mailer = JFactory::getMailer ();
        	$mailer->isHTML ( true );
			$mailer->setSubject ( strip_tags($nof->caption) );
			$mailer->addRecipient ( $nof->rep_email, $nof->rep_name );
			$mailer->setBody ( $body );
			$mailer->Send ();
		}

		return $notifications;
	}

	public function mailq_admin ()
	{
		set_time_limit(0);
		$config = JLexCommentHelper::getConfig ();
		if ( 
			// turn off notification
			$config->def("notification",0)==0
			// email address incorrect
			|| filter_var($config->def("alert_email",""), FILTER_VALIDATE_EMAIL)===false
			// method not cronjob
			|| $config->def("alert_method",0)==1)
		{
			return false;
		}

		// where clause
		$point_time = $config->get("alert_date", JFactory::getDate()->toSql ());
		$whereClauses = array (
				'cm.created_time >= ' . $this->_db->quote($point_time),
				'sent=0'
			);

		$query = $this->_db->getQuery (true);
		$query->select ('SQL_CALC_FOUND_ROWS cm.*, u.name author_name, u.username')
			  ->select('IF(cm.created_by=0,cm.guest_email,u.email) email_owner')
			  ->from ("#__jlexcomment cm")
			  ->leftJoin ("#__users u ON u.id=cm.created_by");

		// entry table
		$query->select ('obj.com_name, obj.com_key, obj.title entry_name, obj.url entry_url')
			  ->leftJoin ('#__jlexcomment_obj obj ON cm.obj_id=obj.id')
			  ->where ('obj.id IS NOT NULL');

		// sticker table
        $query->select ('sticker.path2file sk_path2file')
              ->leftJoin ('#__jlexcomment_sticker AS sticker ON(sticker.id=cm.sticker_id AND sticker.published=1) ');

        $query->where ($whereClauses)
        	  ->order ('cm.created_time DESC');

        $comments = $this->_db->setQuery ($query, 0, 30)->loadObjectList ();
        $total = $this->_db->setQuery("SELECT FOUND_ROWS()")->loadResult();

        $cid = array ();

        if ($comments)
        {
        	$names = array ();

        	foreach ($comments as $k=>&$comment)
        	{
        		$comment->author = $comment->created_by > 0 ? ($config->def('author_name','screen_name')=='screen_name'?$comment->author_name:$comment->username) : $comment->guest_name;

        		if ($config->def("cm_link",0)==1)
	            {
	                $up = CommentHelperAdmin::getObjDetail($comment->com_name, $comment->com_key, $comment->entry_name, $comment->entry_url);
	                $comment->entry_url 	= $up->url;
	                $comment->entry_url 	= str_replace(JUri::root(true), "", $comment->entry_url);
	                $comment->entry_name 	= $up->title;
	            }
        		
        		if (!preg_match('/^https?:\/\//', $comment->entry_url))
	            {
	            	$comment->url = JUri::root() . trim($comment->entry_url,"/");
	            } else {
	            	$comment->url = $comment->entry_url;
	            }

	            $comment->url = $comment->url . (stripos($comment->url, '?') !== false ? '&' : '?') . 'comment_id=' . $comment->id;

	        	// mention
		        $comment->comment = preg_replace_callback('/\{u-([1-9][0-9]*),(.*?)\}/', function($matches){
		            return '<b>@'.$matches[2].'</b>';
		        }, $comment->comment);

		        if (! empty ($comment->comment))
		        {
		        	$comment->comment = $comment->comment;
		        }

	        	if ($comment->sk_path2file!=null)
		        {
		            $comment->sticker = JUri::root() . $comment->sk_path2file;
		            $comment->comment .= "
		                <div class=\"jcm_sticker\">
		                    <img src=\"{$comment->sticker}\" style=\"max-height:200px\" />
		                </div>
		            ";
		        }

		        // attachment
		        $comment->attachments = CommentHelperAdmin::cmAttachment($comment->id);

		        $cid[] = $comment->id;

		        if ($k < 3)
		        	$names [] = $comment->author;
        	}
        }

        if ($total > 0)
        {
        	/**
        	if ( $total > 5)
        	{
        		$title = JText::sprintf("JCM_PEOPLES_COMMENT_POSTING_YOU_FOLLOWING", implode (",", $names), ($total-3));
        	} else {
        		$title = JText::sprintf("JCM_PEOPLES_COMMENT_POSTING_YOU_FOLLOWING_LIMIT", implode (",", $names));
        	}
        	**/
        	$title = JText::_('JCM_NEW_COMMENT_ADD_YOUR_SITE');

        	JLexCommentHelper::mail2admin ($config->get("alert_email"),$config->get("alert_quick_task",1)==1,$comments, $title);
        }

        return null;
	}

	public function stop ()
	{
		$app = JFactory::getApplication();
		$hash = $app->input->getString("hash", "");

		if (empty($hash))
		{
			$this->setError (JText::_("JCM_USER_NOT_FOUND"));
			return false;
		}

		/**
		$query = "DELETE FROM #__jlexcomment_subscribe WHERE email_hash=" . $this->_db->quote ($hash);
		**/

		$query = $this->_db->getQuery(true);
		$query->update('#__jlexcomment_subscribe')
			  ->set('published=0')
			  ->where('email_hash='.$this->_db->quote($hash));

		$this->_db->setQuery($query)->execute();

		$row = $this->_db->setQuery($query)->getAffectedRows();

		if ($row < 1)
		{
			$this->setError (JText::_("JCM_USER_NOT_FOUND"));
			return false;
		}

		return true;
	}
}