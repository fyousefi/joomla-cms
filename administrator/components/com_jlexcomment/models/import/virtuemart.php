<?php
/**
 * @package     JLex Comment
 * @version     1.0.0
 * @copyright   Copyright (C) 2013-2016 JLexArt Team (http://www.jlexart.com). All rights reserved.
 * @license     GNU/GPL or later
 * @author      JLexArt (support@jlexart.com)
 */

defined ( '_JEXEC' ) or die ();

class JLexCommentImportVirtuemart extends JLexCommentImportAdapter
{
	public $name = "Virtuemart";

	private $install = null;

	private function isInstall ()
	{
		if (is_null($this->install))
		{
			$file = JPATH_ADMINISTRATOR . "/components/com_virtuemart/virtuemart.php";
			$this->install = file_exists($file) ? true : false;
		}

		return $this->install;
	}

	public function getCountCm ()
	{
		if (! $this->isInstall())
		{
			return "Not installed";
		}

		$db = JFactory::getDBO ();

		$query = $db->getQuery (true);
		$query->select ('COUNT(*)')
			  ->from ('#__virtuemart_rating_reviews');

		$result = $db->setQuery ($query)->loadResult ();

		return $result;
	}

	public function import ($start = 0, $limit = 50)
	{
		$db = JFactory::getDBO();

		$query = $db->getQuery(true);

		$query->select('c.*, p.product_name entry_title');
		$query->from($db->quoteName('#__virtuemart_rating_reviews') . ' AS c');
		$query->select('u.username as user_username, u.name as user_name, u.email as user_email');
		$query->join('LEFT', $db->quoteName('#__users') . ' AS u ON c.created_by = u.id');
		$query->join('INNER', $db->quoteName('#__virtuemart_products_en_gb') . ' AS p ON c.virtuemart_product_id=p.virtuemart_product_id');
		$query->order($db->escape('c.modified_on'));

		$db->setQuery($query, $start, $limit);
		$rows = $db->loadObjectList();

		if (! $rows)
		{
			return null;
		}

		foreach ($rows as $row) {
			$table = new stdClass ();
			$table->com_key 		= $row->virtuemart_product_id;
			$table->com_name 		= 'virtuemart';
			$table->created_by 		= $row->created_by;
			$table->guest_name 		= $row->user_name;
			$table->comment 		= $row->comment;
			$table->guest_email 	= $row->user_email;
			$table->published 		= $row->published;
			$table->created_time 	= $row->modified_on;
			$table->published 		= $row->published;

			$table->entry_title 	= $row->entry_title;
			$table->url 			= 'index.php?option=com_virtuemart&view=productdetails&virtuemart_product_id=' . $row->virtuemart_product_id;

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