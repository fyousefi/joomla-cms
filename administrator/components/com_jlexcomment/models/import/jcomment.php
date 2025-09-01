<?php
/**
 * @package     JLex Comment
 * @version     1.0.0
 * @copyright   Copyright (C) 2013-2016 JLexArt Team (http://www.jlexart.com). All rights reserved.
 * @license     GNU/GPL or later
 * @author      JLexArt (support@jlexart.com)
 */

defined ( '_JEXEC' ) or die ();

class JLexCommentImportJComment extends JLexCommentImportAdapter
{
	public 	$name = "JComment";

	private $install = null;

	private function isInstall ()
	{
		if (is_null($this->install))
		{
			$file = JPATH_ADMINISTRATOR . "/components/com_jcomments/jcomments.php";
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
			  ->from ('#__jcomments');

		$result = $db->setQuery ($query)->loadResult ();

		return $result;
	}

	public function import ($start = 0, $limit = 50, $parent_id = 0, $new_parent_id = 0)
	{
		$db = JFactory::getDBO();

		$query = $db->getQuery(true);

		$query->select('c.*,  obj.title AS entry_title, obj.link AS entry_link');
		$query->from($db->quoteName('#__jcomments') . ' AS c');
		$query->select('u.id AS created_by');
		$query->join('LEFT', $db->quoteName('#__users') . ' AS u ON c.userid = u.id');
		$query->join('LEFT', $db->quoteName('#__jcomments_objects') . ' AS obj ON (c.object_id=obj.object_id AND c.object_group=obj.object_group)');
		$query->where ("obj.id IS NOT NULL");
		$query->where ("c.parent=" . $parent_id);
		$query->order($db->escape('c.id') . " ASC");

		$db->setQuery($query, $start, $limit);
		$rows = $db->loadObjectList();

		if (! $rows)
		{
			return null;
		}

		foreach ($rows as $row) {
			$table = new stdClass ();
			$table->com_key 		= $row->object_id;
			$table->com_name 		= str_replace("com_", "", $row->object_group);
			$table->created_by 		= $row->created_by > 0 ? $row->created_by : 0;
			$table->guest_name 		= !$row->created_by ? $row->name : '';
			$table->comment 		= $row->comment;
			$table->guest_email 	= !$row->created_by ? $row->email : '';
			$table->published 		= $row->published;
			$table->created_time 	= $row->date;
			$table->published 		= $row->published;
			$table->ip_address 		= $row->ip;

			$table->up_point 		= $row->isgood;
			$table->down_point		= $row->ispoor;

			$table->entry_title 	= $row->entry_title;
			$table->url 			= $row->entry_link;

			if ($new_parent_id>0)
			{
				$table->parent_id = $new_parent_id;
			}

			$id = parent::insert_row ($table);

			if ( $id > 0 )
			{
				$this->total+= 1;

				// import child comments
				$this->import (0, $limit, $row->id, $id);
			}
		}

		return true;

		//$this->import ($start+$limit, $limit, $parent_id, $new_parent_id);
	}
}