<?php
/**
 * @package     JLex Comment
 * @version     1.0.0
 * @copyright   Copyright (C) 2013-2016 JLexArt Team (http://www.jlexart.com). All rights reserved.
 * @license     GNU/GPL or later
 * @author      JLexArt (support@jlexart.com)
 */

defined ( '_JEXEC' ) or die ();

class JLexCommentRouter
{
	public function getUrl($component, $id, $url)
	{
		$router = dirname(__FILE__) ."/". $component . ".php";
		$url = JUri::root(true) . "/" . ltrim($url, "/");

		if (!JFile::exists($router))
		{
			return $url;
		}

		require_once ($router);

		$nameOfClass = "JLexComment" . $component . "Router";
		$router = new $nameOfClass($component, $id);

		$newUrl = $router->getUrl();

		if ($newUrl===false)
		{
			return $url;
		}

		return $newUrl;
	}

	public function getDetail($component, $id, $title='', $url='')
	{
		$router = dirname(__FILE__) . '/' . $component . '.php';
		$url 	= JUri::root(true) . '/' . ltrim($url, '/');
		
		$row 	= new stdClass();
		$row->title = preg_match('/^\s*$/', $title) ? 'Unknow item (id='.$id.')' : $title;
		$row->url 	= $url;

		if (!JFile::exists($router))
		{
			return $row;
		}

		require_once ($router);

		$nameOfClass = 'JLexComment' . $component . 'Router';
		$router = new $nameOfClass($component, $id);

		$nrow = $router->getDetail();

		if ($nrow===false)
		{
			return $row;
		}

		return $nrow;
	}
}