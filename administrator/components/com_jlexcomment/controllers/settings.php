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

class JLexCommentControllerSettings extends JControllerLegacy
{
	public function save()
	{
		$model = $this->getModel ("settings");
		$model->save ();

		$url = JUri::base (true) . '/index.php?option=com_jlexcomment&view=settings';
		$this->setRedirect ($url, JText::_("JCM_SETTING_SAVED"));
	}

	public function import()
	{
		$app = JFactory::getApplication();
		$data = $app->input->post->getString('dt', null);
		$data = json_decode($data);

		if(!$data || !isset($data->version) || !isset($data->params) || !isset($data->name) || $data->name!='jlexcomment') JLexCommentHelper::mix2json(['status'=>400, 'error'=>'Data Structure incorrect.']);

		$db = JFactory::getDbo();
		$query = $db->getQuery(true);

		$query->select('manifest_cache')
			  ->from('#__extensions')
			  ->where([
			  		$db->quoteName('element').'='.$db->quote('com_jlexcomment'),
			  		$db->quoteName('type').'='.$db->quote('component')
			  	]);

		$row = $db->setQuery($query)->loadObject();
		if(!$row) JLexCommentHelper::mix2json(['status'=>400]);

		$manifest = json_decode($row->manifest_cache);
		$version = $manifest->version;

		if($data->version!=$version) JLexCommentHelper::mix2json(['status'=>400, 'error'=>'Extension versions do not match.']);
		
		/* import */
		$query->clear()
			  ->update('#__extensions')
			  ->set('params='.$db->quote(json_encode($data->params)))
			  ->where([
			  		$db->quoteName('element').'='.$db->quote('com_jlexcomment'),
			  		$db->quoteName('type').'='.$db->quote('component')
			  	]);

		$db->setQuery($query)->execute();

		JLexCommentHelper::mix2json(array('status'=>200));
	}

	public function export()
	{
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);

		$query->select('manifest_cache, params')
			  ->from('#__extensions')
			  ->where([
			  		$db->quoteName('element').'='.$db->quote('com_jlexcomment'),
			  		$db->quoteName('type').'='.$db->quote('component')
			  	]);

		$row = $db->setQuery($query)->loadObject();
		if(!$row) JLexCommentHelper::mix2json(['status'=>400]);

		$manifest = json_decode($row->manifest_cache);
		$params = json_decode($row->params);
		
		$dt=['name'=>'jlexcomment', 'version'=>$manifest->version, 'params'=>$params];

		JLexCommentHelper::mix2json(['status'=>200, 'data'=>$dt]);
	}
}
