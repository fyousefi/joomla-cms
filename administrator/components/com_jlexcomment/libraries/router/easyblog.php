<?php
/**
 * @package     JLex Comment
 * @version     1.0.0
 * @copyright   Copyright (C) 2013-2016 JLexArt Team (http://www.jlexart.com). All rights reserved.
 * @license     GNU/GPL or later
 * @author      JLexArt (support@jlexart.com)
 */

defined ( '_JEXEC' ) or die ();

class JLexCommentEasyBlogRouter
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
		$result = $this->getDetail();
		if ($result)
		{
			return $result->url;
		}

		return false;
	}

	public function getDetail()
	{
		$app = JFactory::getApplication();
		$engine = JPATH_ADMINISTRATOR . '/components/com_easyblog/includes/easyblog.php';

		if(JFile::exists($engine)) {
			require_once($engine);

			$post = EB::post($this->id);

			if(!empty($post))
			{
				$info = new stdClass();
				$info->title = $post->title;
				$info->url = JRoute::link('site','index.php?option=com_easyblog&view=entry&id=' . $post->id);

				return $info;
			}
		}
		return false;
	}
}