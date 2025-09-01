<?php
/**
 * @package     JLex Comment
 * @version     1.0.0
 * @copyright   Copyright (C) 2013-2016 JLexArt Team (http://www.jlexart.com). All rights reserved.
 * @license     GNU/GPL or later
 * @author      JLexArt (support@jlexart.com)
 */

defined ( '_JEXEC' ) or die ();

class JLexCommentImportCComment extends JLexCommentImportAdapter
{
	public $name = "CComment";

	private $install = null;

	private function isInstall ()
	{
		if (is_null($this->install))
		{
			$file = JPATH_ADMINISTRATOR . "/components/com_comment/comment.php";
			$this->install = file_exists($file) ? true : false;
		}

		return $this->install;
	}

	public function getCountCm ()
	{
		if (! $this->isInstall())
		{
			return "Not installed.";
		}

		$db = JFactory::getDBO ();

		$query = $db->getQuery (true);
		$query->select ('COUNT(*)')
			  ->from ('#__comment');

		$result = $db->setQuery ($query)->loadResult ();

		return $result;
	}

	public function import ($start = 0, $limit = 50, $parent_id = 0, $new_parent_id = 0)
	{
		$db = JFactory::getDBO();

		$query = $db->getQuery(true);
		if($parent_id<=0) $parent_id=-1;

		$query->select('c.*')
			  ->from($db->quoteName('#__comment') . ' AS c')
			  ->select('u.username as user_username, u.name as user_name, u.email as user_email')
			  ->join('LEFT', $db->quoteName('#__users') . ' AS u ON c.userid = u.id')
			  ->where("c.parentid=" . $db->quote($parent_id))
			  ->order($db->escape('c.id').' ASC');

		$db->setQuery($query, $start, $limit);
		$rows = $db->loadObjectList();

		if (! $rows)
		{
			return null;
		}

		foreach ($rows as $row) {
			$table = new stdClass ();
			$table->com_name 	= str_replace("com_", "", $row->component);;
			$table->com_key  	= $row->contentid;

			$table->comment 	= str_replace(['[b]', '[u]', '[i]', '[s]', '[quote]', '[code]', '[/b]', '[/u]', '[/i]', '[/s]', '[/quote]', '[/code]', "\n"], ['<b>', '<u>', '<i>', '<strike>','<div data-tag="quote">', '<div data-tag="code">', '</b>', '</u>', '</i>', '</strike>', '</div>', '</div>', '<br>'], $row->comment);

			$table->guest_name 	= $row->userid>0?"":$row->name;
			$table->guest_email = $row->userid>0?"":$row->email;
			$table->created_by 	= $row->userid>0?intval($row->userid) : 0;
			$table->created_time = $row->date;
			$table->modified 	= $row->modified;
			$table->published 	= $row->published;
			$table->entry_title = ucfirst($table->com_name).': '.$table->com_key;
			$table->url 		= '';

			$table->ip_address 		= $row->ip;
			$table->up_point 		= $row->voting_yes;
			$table->down_point		= $row->voting_no;

			if ($new_parent_id>0)
			{
				$table->parent_id = $new_parent_id;
			}


			$id = parent::insert_row ($table);
			if($id>0)
			{
				$this->total+= 1;

				// import child comments
				$this->import(0, $limit, $row->id, $id);
			}
		}

		// next
		//$this->import ($start+$limit, $limit);

		return true;
	}
}