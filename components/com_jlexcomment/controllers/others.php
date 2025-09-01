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

class JLexCommentControllerOthers extends JControllerLegacy
{
	public function styles()
	{
		$model  = $this->getModel('others');
		$styles = $model->getStyles();

		JLexCommentHelper::mix2json($styles);
	}

	public function reaction_stic()
	{
		$app 	= JFactory::getApplication();
		$cmid 	= $app->input->getInt('cmid', 0);
		$id 	= $app->input->getInt('id',0);

		if ($id<2 || $cmid<1)
		{
			JLexCommentHelper::mix2json(array(
					'status' => 400,
					'error'  => 'Bad require.'
				));
		}

		$model  = $this->getModel('others');
		$result = $model->getReactions($id, $cmid);

		JLexCommentHelper::mix2json(array(
				'status' => 200,
				'data' 	 => $result
			));
	}

	public function captcha()
	{
		// render captcha from jlexcomment plugin
		$app 	= JFactory::getApplication();
		$plg 	= $app->input->getCmd('plg','');
		$helper = JPATH_ROOT . '/plugins/jlexcomment/'.strtolower($plg).'/helper.php';

		if(!preg_match('/^[A-z0-9]+$/',$plg) || !is_file($helper))
		{
			JLexCommentHelper::mix2json(array(
					'status' => 400,
					'error'  => jtext::_('JCM_PAGE_NOT_FOUND'),
				));
		}

		try {
			require_once $helper;
			$captcha = 'plgJLexComment'.$plg.'Helper';
			$captcha = new $captcha();
			
			$captcha->render();
		} catch(Exception $e) {
			/*echo $e->getMessage();*/
		}
	}

	public function terms()
	{
		$config = JLexCommentHelper::getConfig();
		$data = [
			"content" => ""
		];

		if($config->get("cm_rule",0)==1)
		{
			$data["content"] = $config->get("cm_rule_content");
		}

		JLexCommentHelper::mix2json($data);
	}
}
