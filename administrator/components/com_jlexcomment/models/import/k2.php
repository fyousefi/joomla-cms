<?php
/**
 * @package     JLex Comment
 * @version     1.0.0
 * @copyright   Copyright (C) 2013-2016 JLexArt Team (http://www.jlexart.com). All rights reserved.
 * @license     GNU/GPL or later
 * @author      JLexArt (support@jlexart.com)
 */

defined ( '_JEXEC' ) or die ();

class JLexCommentImportK2 extends JLexCommentImportAdapter
{
	public $name = "K2";

	private $install = null;

	private function isInstall ()
	{
		if (is_null($this->install))
		{
			$file = JPATH_ADMINISTRATOR . "/components/com_k2/k2.php";
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
			  ->from ('#__k2_comments');

		$result = $db->setQuery ($query)->loadResult ();

		return $result;
	}

	public function import ($start = 0, $limit = 50)
	{
		$db = JFactory::getDBO();

		$query = $db->getQuery(true);

		$query->select('c.*, item.title entry_title');
		$query->from($db->quoteName('#__k2_comments') . ' AS c');
		$query->select('u.username as user_username, u.name as user_name, u.email as user_email');
		$query->join('LEFT', $db->quoteName('#__users') . ' AS u ON c.userid = u.id');
		$query->join('LEFT', $db->quoteName('#__k2_items') . ' AS item ON c.itemID=item.id');
		$query->order($db->escape('c.commentDate'));

		$db->setQuery($query, $start, $limit);
		$rows = $db->loadObjectList();

		if (! $rows)
		{
			return null;
		}

		foreach ($rows as $row) {
			$table = new stdClass ();
			$table->com_name 	= 'k2';
			$table->com_key  	= $row->itemID;
			$table->comment 	= $row->commentText;
			$table->guest_name 	= isset($row->userName) ? $row->userName : $row->name;
			$table->guest_email = $row->commentEmail;
			$table->created_by 	= isset($row->userID) ? intval($row->userID) : 0;
			$table->created_time = $row->commentDate;
			$table->published 	= $row->published;
			$table->entry_title = $row->entry_title;
			$table->url 		= 'index.php?option=com_k2&view=item&id=' . $row->itemID;

			if ( parent::insert_row ($table) )
			{
				$this->total+= 1;
			}
		}

		// next
		//$this->import ($start+$limit, $limit);

		return true;
	}
}