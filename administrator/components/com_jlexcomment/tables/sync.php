<?php
/**
 * @package     JLex Comment
 * @version     1.0.0
 * @copyright   Copyright (C) 2013-2016 JLexArt Team (http://www.jlexart.com). All rights reserved.
 * @license     GNU/GPL or later
 * @author      JLexArt (support@jlexart.com)
 */

defined ( '_JEXEC' ) or die ();

class TableCmSync extends JTable
{
	public $id 					= null;
	
	public $object 				= null;

	public $entry_details		= null;

	public $cb_entry_details 	= null;

	public $entry_updated 		= null;

	public $cb_entry_updated 	= null;

	public $author_follow 		= null;

	public $cb_author_follow 	= null;

	public $latest_log 			= null;

	public $published 			= null;

	public $created_by 			= null;

	public $created_time 		= null;

	public $modified_time 		= null;

	public function __construct(&$db)
	{
		parent::__construct ( '#__jlexcomment_sync', 'id', $db );
	}
}