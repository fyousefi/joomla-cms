<?php
/**
 * @package     JLex Comment
 * @version     1.0.0
 * @copyright   Copyright (C) 2013-2016 JLexArt Team (http://www.jlexart.com). All rights reserved.
 * @license     GNU/GPL or later
 * @author      JLexArt (support@jlexart.com)
 */

defined ( '_JEXEC' ) or die ();

class JLexCommentViewItems extends JViewLegacy
{
	public $data = null;

	public function display($tpl = null)
	{
		// Parameters
		$app       	= JFactory::getApplication();
		$doc       	= JFactory::getDocument();
		$params    	= $app->getParams();
		$feedEmail 	= $app->get('feed_email', 'author');
		$siteEmail 	= $app->get('mailfrom');

		if (! count($this->data->comments))
		{
			return;
		}

		foreach ($this->data->comments as $row)
		{
			// Strip html from feed item title
			//$title = $this->escape($row->title);
			//$title = html_entity_decode($title, ENT_COMPAT, 'UTF-8');

			// Url link to article
			$link = JUri::root () . "/" . ltrim($this->data->object->url,"/") . "?comment_id=" . $row->id;

			// Load individual item creator class
			$item           = new JFeedItem;
			//$item->title    = $title;
			$item->link     = $link;
			$item->date     = $row->created_og;
			$item->author 	= $row->author_name;

			// Load item description and add div
			$item->description = '<div class="feed-description">' . $row->comment . '</div>';

			// Loads item info into rss array
			$doc->addItem($item);
		}
	}
}
