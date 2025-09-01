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

class JLexCommentControllerSync extends JControllerLegacy
{
	public function save()
	{
		$app    = JFactory::getApplication();
        $user   = JFactory::getUser();

        $errorMsg = array();

        $model  = $this->getModel("sync");
        $return = $model->save();

        if ($return===false)
        {
            $errors = $model->getErrors();

            foreach ($errors as $error)
            {
                if ($error instanceof Exception)
                {
                    $app->enqueueMessage($error->getMessage(), 'error');
                } else {
                    $app->enqueueMessage($error, 'error');
                }
            }

            // create cache variable
            $data = array_key_exists("jform", $_POST) ? $_POST["jform"] : null;

            $app->setUserState('jcm.sync', $data);
            
            $this->setRedirect(JUri::base(true).'/index.php?option=com_jlexcomment&view=sync&layout=form');
            return;
        }


        $this->setRedirect( JUri::base(true).'/index.php?option=com_jlexcomment&view=sync', jtext::_('JCM_SYNC_ITEM_SAVED'));
	}

	public function cancel()
	{
		$this->setRedirect( JUri::base(true).'/index.php?option=com_jlexcomment&view=sync' );
	}

	public function add()
	{
		$this->setRedirect( JUri::base(true).'/index.php?option=com_jlexcomment&view=sync&layout=form' );
	}

	public function publish()
	{
		$this->listCmd('publish');
		$this->setRedirect(JUri::base(true).'/index.php?option=com_jlexcomment&view=sync', jtext::_('JCM_ROWS_PUBLISHED'));
	}

	public function unpublish()
	{
		$this->listCmd('unpublish');
		$this->setRedirect(JUri::base(true).'/index.php?option=com_jlexcomment&view=sync', jtext::_('JCM_ROWS_UNPUBLISHED'));
	}

	public function sync()
	{
		$app = JFactory::getApplication();
		$offset = $app->input->getInt('offset', 0);

		$model = $this->getModel('sync');
		if($offset>0) $model->set('offset', $offset);

		$result = $model->sync();

		JLexCommentHelper::mix2json($result);
	}

	protected function listCmd($action)
	{
		$app = JFactory::getApplication();
		$cid = $app->input->get("cid",null,"array");

    	if(!$cid || !count($cid))
    	{
    		throw new Exception( jtext::_('JCM_MAKE_SELECTION_FROM_THE_LIST'), 500 );
    		return false;
    	}

    	$model = $this->getModel('sync');
    	$model->listCmd($action, $cid);

    	return true;
	}
}
