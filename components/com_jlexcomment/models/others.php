<?php
/**
 * @package     JLex Comment
 * @version     1.0.0
 * @copyright   Copyright (C) 2013-2016 JLexArt Team (http://www.jlexart.com). All rights reserved.
 * @license     GNU/GPL or later
 * @author      JLexArt (support@jlexart.com)
 */

defined ( '_JEXEC' ) or die ();

class JLexCommentModelOthers extends JModelLegacy
{
	public function getStyles()
	{
		$query = $this->_db->getQuery(true);
		$query->select('caption,css,maxlength,id')
			  ->from('#__jlexcomment_style')
			  ->where('published=1')
			  ->order('caption ASC');

		$styles = $this->_db->setQuery($query)->loadObjectList();

		return $styles;
	}

	public function getReactions($id, $cmid)
	{
		$user   = JFactory::getUser();
		$config = JLexCommentHelper::getConfig ();

		$query = $this->_db->getQuery(true);
		$query->select('SQL_CALC_FOUND_ROWS u.username, u.name')
			  ->select('IF(u.id>0,1,0) member')
			  ->select('IF(u.id='.$user->id.',1,0) me')
			  ->from('#__jlexcomment_vote v')
			  ->leftJoin('#__users AS u ON v.created_by=u.id')
			  ->where(array(
			  		'comment_id=' . $cmid,
			  		'point=' . $id
			  	))
			  ->order('me DESC, member DESC, RAND()');

		$list = $this->_db->setQuery($query,0,7)->loadObjectList();

		$result = new stdClass();
		$result->you 		= 0;
		$result->members 	= array();
		$result->total 		= $this->_db->setQuery("SELECT FOUND_ROWS()")
                                    ->loadResult();

		if (!$list) return $result;

		foreach( $list as $k=>$v)
		{
			if ($v->me==1)
			{
				$result->you=1;
			} elseif($v->member==1) {
				$result->members[] = $config->get('author_name','screen_name')=='screen_name'?$v->name:$v->username;
			}
		}

		return $result;
	}
}