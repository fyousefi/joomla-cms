<?php
/**
 * @version		1.0.0
 * @package		JLex Comment
 * @subpackage	Module JLex Comment
 * @copyright	Copyright (C) 2013-2016 JLexArt. All rights reserved.
 * @license		GNU/GPL 2 or later
 * @author		JLexArt
 */
defined ( "_JEXEC" ) or die;

class ModJLexCommentHelper
{
	protected 	$config;

	protected 	$params;

	protected 	$_db;

	protected 	$pagination = null;

	public function __construct($config, $params)
	{
		$this->_db = JFactory::getDbo();
		$this->config = $config;
		$this->params = $params;
	}

	protected function limited() {
		$whereClause = array ();
		$matches = null;
		$areas = $this->params->get( 'area', array () );

		if (in_array("0", $areas))
		{
			$areas = array();
		}
		
		// extra area, use if component not found in system
		$areaExtra = $this->params->get ( 'area_extra', '' );
		preg_match_all ( '/[A-z0-9\_]+/m', $areaExtra, $matches );

		if ($areaExtra && isset ( $matches [0] ) && count ( $matches [0] )) {
			$areas = array_merge ( $areas, $matches [0] );
		}
		
		// by component
		if (is_array ( $areas ) && count ( $areas )) {
			$whereClause [] = "o.com_name IN ('" . implode ( "','", $areas ) . "')";
		}

		// by item id
		$objectCid = $this->params->get( 'entry_ids', '' );
		preg_match_all ( '/[0-9]+/m', $objectCid, $matches );
		if ($objectCid && isset ( $matches [0] ) && count ( $matches [0] )) {
			$whereClause [] = "o.com_key IN (" . implode ( ",", $this->_db->quote($matches[0]) ) . ")";
		}

		return $whereClause;
	}

	public function getPagination ()
	{
		return $this->pagination;
	}


	public function latestComments() {
		$now 	= JFactory::getDate()->toSql();
		$user 	= JFactory::getUser();
		$app 	= JFactory::getApplication();

		if ($this->params->get ( 'pagination',0)==0)
		{
			$limitstart = 0;
		} else {
			$varname = "limitstart";
			if (preg_match("/^[A-z]+$/", $this->params->get("page_prefix","")))
			{
				$varname = $this->params->get("page_prefix") . $varname;
			}
			$limitstart = $app->input->getInt($varname);
			$limitstart = $limitstart < 1 ? 0 : $limitstart;
		}

		$whereClauses = array(
				"cm.published=1",
				"cm.created_time<=" . $this->_db->quote( $now ),
				"o.id IS NOT NULL"
			);

		if ($this->params->def("lc_reply_comment",1)==0)
		{
			$whereClauses[] = "cm.parent_id=0";
		}

		$query = $this->_db->getQuery(true);
		$query->select ('SQL_CALC_FOUND_ROWS cm.*')
              ->from ('#__jlexcomment AS cm')
              ->select('u.username,u.name screen_name, u.email user_email')
              ->leftJoin ('#__users AS u ON u.id=cm.created_by')
              ->where ($whereClauses)

              ->select( 'o.title entry_title, o.url entry_url, o.com_name, o.com_key' )
              ->leftJoin( '#__jlexcomment_obj o ON o.id=cm.obj_id' )
        	  
        	  // sticker table
        	  ->select ('sticker.path2file sk_path2file')
              ->leftJoin ('#__jlexcomment_sticker AS sticker ON(sticker.id=cm.sticker_id AND sticker.published=1) ')
              ->order ('cm.created_time DESC');

        $area = $this->limited();
        if (is_array($area) && count($area) > 0)
        {
        	$query->where( $area );
        }
		
		$comments 	= $this->_db->setQuery( $query, $limitstart, $this->params->get('limit', 10)*1 )->loadObjectList();
		$total   	= $this->_db->setQuery( "SELECT FOUND_ROWS()" )->loadResult();

		if (!$comments)
		{
			return null;
		}

		if ($this->params->get('pagination',0)==1)
		{
			$prefix = "";
			if (preg_match("/^[A-z]+$/", $this->params->get("page_prefix","")))
			{
				$prefix = $this->params->get("page_prefix");
			}

			jimport ( 'joomla.html.pagination' );
			$this->pagination = new JPagination ( $total, $limitstart, $this->params->get ( 'limit', 10 ) * 1, $prefix );
		}

		$profileClass 	 = JLexCommentHelper::getProfile ();
		$model 			 = new JLexCommentModelItems();
		$model->set("jcm_user", $user);

		foreach ($comments as $k=>&$cm)
		{
			if ($this->params->def("lc_limit_words",0) > 0)
			{
				$cm->comment = strip_tags($cm->comment);
				$cm->comment = $this->shortString( $cm->comment, $this->params->def("lc_limit_words")*1);
			}
			$model->_formatComment( $cm );
			
			if ($this->config->def("cm_link",0)==1)
			{
				if (method_exists('CommentHelperAdmin','getObjDetail'))
				{
					$up = CommentHelperAdmin::getObjDetail($cm->com_name, $cm->com_key,$cm->entry_title , $cm->entry_url);
					$cm->url = $up->url;
					$cm->entry_title = $up->title;
					$cm->url = $cm->url . (preg_match("/\?/", $cm->url)?'&':'?') . "comment_id=" . $cm->id;
				} else {
					$url = CommentHelperAdmin::getRelativeUrl($cm->com_name, $cm->com_key, $cm->entry_url);
					$cm->url = $url . (preg_match("/\?/", $url)?'&':'?') . "comment_id=" . $cm->id;
				}
			} else {
				$cm->url = JUri::base() . ltrim($cm->entry_url,"/") . (preg_match("/\?/", $cm->entry_url)?'&':'?') . "comment_id=" . $cm->id;
			}

			if ($this->params->def("lc_media",1)==0)
			{
				$cm->media = null;
			}

			if ($this->params->def("lc_avatar",1)==0)
			{
				$cm->author_thumb = null;
			}

			if ($this->params->def("lc_date",1)==0)
			{
				$cm->created_time = null;
			}

			if ($this->params->def("lc_entry",1)==0)
			{
				$cm->entry_title = null;
			}

			if ($this->params->def("lc_reply_parent",1)==1 && $cm->parent_id>0)
			{
				$subQuery = $this->_db->getQuery(true);
				$subQuery->select( "cm.created_by,cm.guest_name, u.name, u.username" )
						 ->from( "#__jlexcomment AS cm")
						 ->leftJoin( "#__users AS u ON u.id=cm.created_by" )
						 ->where( "cm.id=" . $cm->parent_id );

				$row = $this->_db->setQuery($subQuery,0,1)->loadObject();
				if ($row)
				{
					$author = !$row->created_by ? $row->guest_name : ($this->config->def('author_name','screen_name')=='screen_name'?$row->name:$row->username);
					$cm->comment = '<span class="jcm-mod-parent">@'.$author.'</span>' . $cm->comment;
				}
			}
		}

		return $comments;
	}

	public function getMostCommented() 
	{
		$now = JFactory::getDate()->toSql();
		$app = JFactory::getApplication();

		if ($this->params->get ( 'pagination',0)==0)
		{
			$limitstart = 0;
		} else {
			$varname = "limitstart";
			if (preg_match("/^[A-z]+$/", $this->params->get("page_prefix","")))
			{
				$varname = $this->params->get("page_prefix") . $varname;
			}
			$limitstart = $app->input->getInt ($varname);
			$limitstart = $limitstart < 1 ? 0 : $limitstart;
		}

		$whereClauses = array(
				"o.published=1",
				"o.created_time<=" . $this->_db->quote( $now )
			);

		$query = $this->_db->getQuery(true);
		$query->select("SQL_CALC_FOUND_ROWS o.title, o.url, o.com_name, o.com_key")
			  ->from( "#__jlexcomment_obj o" )
			  ->where( $whereClauses );

		if ($this->params->def("mc_count_cm_reply",1)==1)
		{
			$query->select( "o.cm_count_active cm_count" )
				  ->where( "o.cm_count_active>0" )
				  ->order( "o.cm_count_active DESC" );
		} else {
			$query->select( "o.cm_i_count_active cm_count" )
				  ->where( "o.cm_i_count_active>0" )
				  ->order( "o.cm_i_count_active DESC" );
		}

		$area = $this->limited();
        if (is_array($area) && count($area) > 0)
        {
        	$query->where( $area );
        }

        $entries 	= $this->_db->setQuery( $query, $limitstart, $this->params->get('limit', 10)*1 )->loadObjectList();
		$total   	= $this->_db->setQuery( "SELECT FOUND_ROWS()" )->loadResult();

		if (! $entries)
		{
			return null;
		}

		if ($this->params->get('pagination',0)==1)
		{
			$prefix = "";
			if (preg_match("/^[A-z]+$/", $this->params->get("page_prefix","")))
			{
				$prefix = $this->params->get("page_prefix");
			}

			jimport ( 'joomla.html.pagination' );
			$this->pagination = new JPagination ( $total, $limitstart, $this->params->get ( 'limit', 10 ) * 1, $prefix );
		}

		foreach ($entries as $k=>&$entry)
		{
			$entry->index 	= $limitstart + $k + 1;
			$entry->cm_count = intval( $entry->cm_count );

			if ($this->config->def("cm_link",0)==1)
			{
				if (method_exists('CommentHelperAdmin','getObjDetail'))
				{
					$up = CommentHelperAdmin::getObjDetail($entry->com_name, $entry->com_key,$entry->title , $entry->url);
					$entry->url = $up->url;
					$entry->title = $up->title;
				} else {
					$entry->url = CommentHelperAdmin::getRelativeUrl($entry->com_name, $entry->com_key, $entry->url);
				}
				$entry->url = rtrim($entry->url,"/") . "#comment";
			} else {
				$entry->url 	= JUri::base(true) . "/" . ltrim($entry->url,"/") . "#comment";
			}
		}

		return $entries;
	}

	public function getTopPosters()
	{
		$now = JFactory::getDate()->toSql();
		$app = JFactory::getApplication();

		if ($this->params->get ( 'pagination',0)==0)
		{
			$limitstart = 0;
		} else {
			$varname = "limitstart";
			if (preg_match("/^[A-z]+$/", $this->params->get("page_prefix","")))
			{
				$varname = $this->params->get("page_prefix") . $varname;
			}
			$limitstart = $app->input->getInt ($varname);
			$limitstart = $limitstart < 1 ? 0 : $limitstart;
		}

		$whereClauses = array(
				"cm.published=1",
				"cm.created_time<=" . $this->_db->quote( $now ),
				"cm.created_by>0", // make sure that is member
				"o.id IS NOT NULL",
				"u.id IS NOT NULL"
			);

		$period = intval($this->params->def("tp_period",0));
		if ( $period > 0)
		{
			$whereClauses[] = "cm.created_time >= DATE_SUB(".$this->_db->quote( $now )." , INTERVAL {$period} DAY)";
		}

		if ($this->params->def("tp_count_cm_reply",1)==0)
		{
			$whereClauses[] = "cm.parent_id=0";
		}

		$query = $this->_db->getQuery(true);
		$query->select("SQL_CALC_FOUND_ROWS COUNT(cm.id) cm_count, u.email, u.id")
			  ->from( "#__jlexcomment cm" )
			  ->leftJoin( "#__jlexcomment_obj o ON o.id=cm.obj_id" )
			  ->leftJoin( "#__users u ON u.id=cm.created_by")
			  ->where( $whereClauses )
			  ->group( "cm.created_by" )
			  ->order( "cm_count DESC");

		if ($this->config->def('author_name','screen_name')=='screen_name')
		{
			$query->select("u.name author_name");
			$query->order( "u.name DESC" );
		} else {
			$query->select("u.username author_name");
			$query->order( "u.username DESC" );
		}

		$area = $this->limited();
        if (is_array($area) && count($area) > 0)
        {
        	$query->where( $area );
        }

        $users 	= $this->_db->setQuery( $query, $limitstart, $this->params->get('limit', 10)*1 )->loadObjectList();
		$total  = $this->_db->setQuery( "SELECT FOUND_ROWS()" )->loadResult();

		if (! $users)
		{
			return null;
		}

		if ($this->params->get('pagination',0)==1)
		{
			$prefix = "";
			if (preg_match("/^[A-z]+$/", $this->params->get("page_prefix","")))
			{
				$prefix = $this->params->get("page_prefix");
			}

			jimport ( 'joomla.html.pagination' );
			$this->pagination = new JPagination ( $total, $limitstart, $this->params->get ( 'limit', 10 ) * 1, $prefix );
		}

		$profileClass  = JLexCommentHelper::getProfile ();
		foreach ($users as $k=>&$user)
		{
			$profile 			= $profileClass->getUser ($user->id, $user->email);
			$user->thumbnail 	= $profile ['thumb'];
			$user->url 			= $profile ['link'];

			if ($this->params->def("tp_thumbnail",1)==0)
			{
				$user->thumbnail = null;
			}
		}

		return $users;
	}

	public function shortString($text, $word = 0, $after_text = '') {
		if ($word == 0)
			return $text;
		$text_piece = explode ( ' ', $text );
		if (count ( $text_piece ) <= $word)
			return $text;
		$new_string = '';
		for($i = 0; $i < $word; $i ++) {
			$new_string .= $text_piece [$i] . ' ';
		}
		$new_string .= '... ' . $after_text;
		return $new_string;
	}
}
