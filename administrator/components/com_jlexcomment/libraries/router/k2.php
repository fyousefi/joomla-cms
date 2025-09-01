<?php
/**
 * @package     JLex Comment
 * @version     1.0.0
 * @copyright   Copyright (C) 2013-2016 JLexArt Team (http://www.jlexart.com). All rights reserved.
 * @license     GNU/GPL or later
 * @author      JLexArt (support@jlexart.com)
 */

defined ( '_JEXEC' ) or die ();

class JLexCommentK2Router
{
	protected $component 	= '';

	protected $id 			= null;

	public function __construct($component, $id)
	{
		$this->component = $component;
		$this->id = $id;
	}

	public function getUrl()
	{
		$app = JFactory::getApplication();
		
		$routerHelper = JPATH_SITE.'/components/com_k2/helpers/route.php';

		if (is_file($routerHelper))
		{
			if (!class_exists('K2HelperRoute')) {
				require_once($routerHelper);
			}

			$db = JFactory::getDBO();
			$query = "SELECT i.id, i.title, i.catid, i.alias, i.access, i.created_by, c.alias as catalias"
				. " FROM #__k2_items as i"
				. " LEFT JOIN #__k2_categories as c ON c.id=i.catid"
				. " WHERE i.id = " . $this->id;
			$db->setQuery($query);
			$row = $db->loadObject();
			
			if (!empty($row)) {
				$url = K2HelperRoute::getItemRoute($row->id.':'.urlencode($row->alias), $row->catid.':'.urlencode($row->catalias));
			
				if ($app->isClient('site'))
				{
					$url = JRoute::_($url);
				} else {
					$app    = JApplication::getInstance('site');
					$router = $app->getRouter(); 

					$url = $router->build($url);
					$url = $url->toString();
					$url = str_replace('/administrator', '', $url);
				}

				return $url;
			}
		}

		return false;
	}

	public function getDetail()
	{
		$app = JFactory::getApplication();
		
		$routerHelper = JPATH_SITE.'/components/com_k2/helpers/route.php';

		if (is_file($routerHelper))
		{
			if (!class_exists('K2HelperRoute')) {
				require_once($routerHelper);
			}

			$db = JFactory::getDBO();
			$query = "SELECT i.id, i.title, i.catid, i.alias, i.access, i.created_by, c.alias as catalias"
				. " FROM #__k2_items as i"
				. " LEFT JOIN #__k2_categories as c ON c.id=i.catid"
				. " WHERE i.id = " . $this->id;
			$db->setQuery($query);
			$article = $db->loadObject();
			
			if (!empty($article)) {
				$url = K2HelperRoute::getItemRoute($article->id.':'.urlencode($article->alias), $article->catid.':'.urlencode($article->catalias));
			
				if ($app->isClient('site'))
				{
					$url = JRoute::_($url);
				} else {
					$app    = JApplication::getInstance('site');
					$router = $app->getRouter(); 

					$url = $router->build($url);
					$url = $url->toString();
					$url = str_replace('/administrator', '', $url);
				}

				$row = new stdClass();
				$row->title = $article->title;
				$row->url 	= $url;

				return $row;
			}
		}

		return false;
	}
}