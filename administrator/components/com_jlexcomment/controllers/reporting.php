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

class JLexCommentControllerReporting extends JControllerLegacy
{
	public function add2blacklist ()
	{
		$model = $this->getModel ('reporting');
		$return = $model->add2blacklist ();

		$url = JUri::base (true) . "/index.php?option=com_jlexcomment&view=reporting";

		if ( $return==false )
		{
			$this->setRedirect ( $url, $model->getError(), "error" );
			return false;
		}

		$this->setRedirect ( $url, JText::_("JCM_ITEM_ADDED_TO_BLACKLIST") );
	}

	public function delete ()
	{
		$model = $this->getModel ('reporting');
		$return = $model->remove ();

		$msg = JText::_("JCM_ROWS_DELETED");

		$url = JUri::base (true) . '/index.php?option=com_jlexcomment&view=reporting';
		$this->setRedirect ($url, $msg);
	}
}
