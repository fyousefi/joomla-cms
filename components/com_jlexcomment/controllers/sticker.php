<?php
/**
 * @package     JLex Comment
 * @version     1.0.0
 * @copyright   Copyright (C) 2013-2016 JLexArt Team (http://www.jlexart.com). All rights reserved.
 * @license     GNU/GPL or later
 * @author      JLexArt (support@jlexart.com)
 */

defined ( '_JEXEC' ) or die ();

require_once JPATH_COMPONENT . '/controller.php';

class JLexCommentControllerSticker extends JControllerLegacy
{
	public function group ()
	{
		$app = JFactory::getApplication();

		$this->addModelPath ( JPATH_ROOT . '/administrator/components/com_jlexcomment/models', 'JLexComment' );

		$model = $this->getModel ('sticker');
		$list = $model->list_group ();

		if ( count($list) )
		{
			foreach ($list as $k=>&$group)
			{
				unset ($group->created_by);
				unset ($group->created_time);
				unset ($group->auto_post);
				unset ($group->published);
			}
		} else {
			$list = array ();
		}

		JLexCommentHelper::mix2json ($list);
	}

	public function load ()
	{
		$app = JFactory::getApplication();
		$id  = $app->input->getInt ('id', 0);

		$this->addModelPath ( JPATH_ROOT . '/administrator/components/com_jlexcomment/models', 'JLexComment' );

		$model = $this->getModel ('sticker');
		$model->set ('group_id', $id);

		$stickers = $model->load_group ();

		JLexCommentHelper::mix2json ($stickers);
	}

	public function upload ()
	{
		$this->addModelPath ( JPATH_ROOT . '/administrator/components/com_jlexcomment/models', 'JLexComment' );
		$model = $this->getModel ('sticker');

		$return = $model->upload ();
		if (! $return)
		{
			$response = array (
				'status' => 400,
				'error' => $model->getError ()
			);
		} else {
			$data = array (
				'id' => $return->id,
				'url' => JUri::root (true) . '/' . $return->path2file
			);

			$response = array (
				'status' => 200,
				'data' => $data
			);
		}

		JLexCommentHelper::mix2json ($response);
	}
}
