<?php
/**
 * @package     JLex Comment
 * @version     1.0.0
 * @copyright   Copyright (C) 2013-2016 JLexArt Team (http://www.jlexart.com). All rights reserved.
 * @license     GNU/GPL or later
 * @author      JLexArt (support@jlexart.com)
 */

defined ( '_JEXEC' ) or die ();

class JLexCommentImportAdapter
{
	public $total = 0;
	
	public function insert_row ( $data )
	{
		$table = JTable::getInstance('Comment', 'TableCm');

		// try check to object/entry
		$entry = JTable::getInstance('Object', 'TableCm');
		$entry->load (array(
				'com_name' => $data->com_name,
				'com_key'  => $data->com_key
			));

		if ( ! $entry->id )
		{
			$entry->bind (array(
					'title' 		=> $data->entry_title,
					'com_name' 		=> $data->com_name,
					'com_key' 		=> $data->com_key,
					'created_by' 	=> JFactory::getUser ()->id,
					'created_time' 	=> JFactory::getDate()->toSql (),
					'url' 			=> $data->url
				));

			$entry->check ();
			$entry->store ();
		}

		if (!$entry->id)
		{
			return false;
		}

		// unset some variables
		unset ($data->entry_title);
		unset ($data->com_name);
		unset ($data->com_key);
		unset ($data->url);

		$table->obj_id = $entry->id;
		$table->bind ($data);

		if (!$table->store())
		{
			return false;
		}

		return $table->id;
	}
}