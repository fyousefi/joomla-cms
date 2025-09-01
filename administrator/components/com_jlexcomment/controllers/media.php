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

class JLexCommentControllerMedia extends JControllerLegacy
{
	public function download()
	{
		$app 	= JFactory::getApplication();
		$id 	= $app->input->getInt('id', 0);

		$model = $this->getModel('media');
		$model->set ('id', $id);

		$return = $model->download();

		if($return==false)
		{
			throw new Exception($model->getError(), 404);
			return false;
		}
	}

	public function upload ()
	{
		$app 	= JFactory::getApplication();
		$id 	= $app->input->getInt ('id', 0);

		$this->addModelPath ( JPATH_ROOT . '/administrator/components/com_jlexcomment/models', 'JLexComment' );

		$model = $this->getModel ('media');
		$return = $model->upload ();

		if (! $return)
		{
			$response = array (
				'status' => 400,
				'error' => $model->getError ()
			);
		} else {
			$response = array (
				'status' => 200,
				'id' => $return
			);
		}
		
		JLexCommentHelper::mix2json ( $response );
	}

	public function update ()
	{
		$app 	= JFactory::getApplication();
		$id 	= $app->input->getInt ('id', 0);

		$model = $this->getModel ('media');
		$return = $model->update ();

		if (! $return)
		{
			$response = array (
				'status' => 400,
				'error' => $model->getError ()
			);
		} else {
			$response = array (
				'status' => 200
			);
		}
		
		JLexCommentHelper::mix2json ( $response );
	}

	public function clean()
	{
		$app 	= JFactory::getApplication();
		
		$model = $this->getModel ('media');
		$return = $model->clean();

		JLexCommentHelper::mix2json(array('status'=>200 , 'count'=>$return));
	}
}
