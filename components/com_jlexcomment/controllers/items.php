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

class JLexCommentControllerItems extends JControllerLegacy
{
	public function reload()
	{
		$app 		= JFactory::getApplication();
		$request 	= $app->input;

		$key 	= $request->getString('key', '');
		$parse 	= JLexCommentHelper::decodeSub($key);

		if(!$parse)
		{
			$response = array (
				'status' => 400,
				'error'  => "Key incorrect."
			);
			JLexCommentHelper::mix2json ($response);
		}


    	$model = $this->getModel("items");
    	$model->set("com_name", $parse->com_name );
    	$model->set("com_key", $parse->com_key );

    	// filter variable
    	$ft_parent_id 			= $request->getInt('parent_id', 0);
    	$ft_timestamp 			= $request->getInt('timestamp', 0);
    	$ft_timestamp_offset 	= $request->getInt('timestamp_offset', 0);
    	$ft_page 				= $request->getInt('page_comment', 1);
    	$ft_sort 				= $request->getCmd('sort', 'best');
        $ft_cmid = $request->getInt('cmid', 0);

    	if($ft_parent_id>0)
            $model->set("parent_id", $ft_parent_id);

    	if($ft_timestamp>0)
            $model->set("timestamp", $ft_timestamp);

    	if($ft_timestamp_offset>0)
            $model->set("timestamp_offset", $ft_timestamp_offset);

    	if($ft_page>1)
            $model->set("page", $ft_page);

    	if($ft_sort!='best')
            $model->set("sort", $ft_sort);

        if($ft_cmid>0)
            $model->set('comment_id', $ft_cmid);

        $view = $this->getView("items", "html");
    	$view->setModel($model, true);

    	$unwrap = $request->getBool ('unwrap', true);
    	$view->set('unwrap', $unwrap);

    	// add template path
    	JLexCommentHelper::loadThemes($view);
    	$view->setLayout("default_comments");

    	$response = array (
				'status' 	=> 200,
				'timestamp' => JFactory::getDate()->toUnix() + 1,
				'html'  	=> $view->render()
		);

		JLexCommentHelper::mix2json($response);
	}

    public function loadCm ()
    {
        $app        = JFactory::getApplication();
        $request    = $app->input;

        $id  = $request->getInt ('id', 0);

        $model = $this->getModel ("items");
        $model->set ('id', $id);

        $comment = $model->getComment ();
        if (! $comment)
        {
            $response = array (
                    'status' => 400,
                    'error'  => $model->getError ()
                );
        } else {
            $view = $this->getView ("items", "html");
            // add template path
            JLexCommentHelper::loadThemes ($view);
            $view->cm = $comment;

            $response = array (
                    'status' => 200,
                    'html' => $view->renderCm()
                );
        }

        JLexCommentHelper::mix2json ($response);
    }
}