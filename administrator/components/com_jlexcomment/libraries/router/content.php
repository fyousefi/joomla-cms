<?php
/**
 * @package     JLex Comment
 * @version     1.0.0
 * @copyright   Copyright (C) 2013-2016 JLexArt Team (http://www.jlexart.com). All rights reserved.
 * @license     GNU/GPL or later
 * @author      JLexArt (support@jlexart.com)
 */

defined ( '_JEXEC' ) or die;

use Joomla\CMS\Router\Route;
use Joomla\Component\Content\Site\Helper\RouteHelper;

class JLexCommentContentRouter
{
	protected $component 	= '';

	protected $id 			= null;

	public function __construct($component, $id)
	{
		$this->component = $component;
		$this->id = $id;
	}

	// ignore from v1.3.3
	public function getUrl()
	{
		$app = JFactory::getApplication();
		require_once JPATH_ROOT . '/components/com_content/helpers/route.php';

		$db = JFactory::getDbo();
		$query = $db->getQuery(true);


		$query->select('a.id, a.title, a.created_by, a.access, a.alias, a.catid, a.language')
			  ->from('#__content AS a')
			  ->select('c.title AS category_title, c.path AS category_route, c.access AS category_access, c.alias AS category_alias')
			  ->join('LEFT', '#__categories AS c ON c.id = a.catid')
			  ->where('a.id = ' . (int) $this->id);

		$db->setQuery( $query );
		$article = $db->loadObject();

		if(!empty($article))
		{
			$user = JFactory::getUser();

			$article->slug = $article->alias ? ($article->id.':'.$article->alias) : $article->id;
			$article->catslug = $article->category_alias ? ($article->catid.':'.$article->category_alias) : $article->catid;

			$url = ContentHelperRoute::getArticleRoute($article->slug, $article->catslug, $article->language);

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

		return false;
	}

	public function getDetail()
	{
		$app = JFactory::getApplication();
		$loader = JPATH_ROOT . '/components/com_content/helpers/route.php';

		if(file_exists($loader)) require_once $loader;

		$db = JFactory::getDbo();
		$query = $db->getQuery(true);


		$query->select('a.id, a.title, a.created_by, a.access, a.alias, a.catid, a.language')
			  ->from('#__content AS a')
			  ->select('c.title AS category_title, c.path AS category_route, c.access AS category_access, c.alias AS category_alias')
			  ->join('LEFT', '#__categories AS c ON c.id = a.catid')
			  ->where('a.id = ' . (int) $this->id);

		$db->setQuery( $query );
		$article = $db->loadObject();

		if(!empty($article))
		{
			$user = JFactory::getUser();

			$article->slug = $article->alias ? ($article->id.':'.$article->alias) : $article->id;
			$article->catslug = $article->category_alias ? ($article->catid.':'.$article->category_alias) : $article->catid;

			if(JE_JVERSION=="J3")
			{
				$url = ContentHelperRoute::getArticleRoute($article->slug, $article->catslug, $article->language);

				if(version_compare(JVERSION, "3.9", ">="))
				{
					$url = JRoute::link("site", $url);
				} else {
					if($app->isClient('site'))
					{
						$url = JRoute::_($url);
					} else {
						$app    = JApplication::getInstance('site');
						$router = $app->getRouter(); 

						$url = $router->build($url);
						$url = $url->toString();
						$url = str_replace('/administrator', '', $url);
					}
				}
			} else {
				$url = Route::link("site", RouteHelper::getArticleRoute($article->slug, $article->catid, $article->language));
			}
			

			$row = new stdClass();
			$row->title = $article->title;
			$row->url 	= $url;

			return $row;
		}

		return false;
	}
}