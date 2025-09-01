<?php
/**
 * @package     JLex Comment
 * @version     1.0.0
 * @copyright   Copyright (C) 2013-2016 JLexArt Team (http://www.jlexart.com). All rights reserved.
 * @license     GNU/GPL or later
 * @author      JLexArt (support@jlexart.com)
 */

defined ( '_JEXEC' ) or die ();

class JLexCommentModelReport extends JModelLegacy
{
	public $id = 0;

	public function report ()
	{
		$request 	= JFactory::getApplication()->input;
		$id 		= $request->getInt ('id', 0);
		$config 	= JLexCommentHelper::getConfig ();

		if ($config->get("u_report_comment",false)==false)
		{
			$this->setError (JText::_("JCM_THIS_FEATURE_IS_DISABLED"));
			return false;
		}

		if ( $id < 1 )
		{
			$this->setError (JText::_("JCM_PERMISSION_DENIED"));
			return false;
		}

		$row = $this->getTable ('report', 'TableCm');
		$user = JFactory::getUser ();
		$ip_address = JLexCommentHelper::ip_address();

		$conditions = array ('comment_id' => $id);
		if ($user->guest)
		{
			$conditions ['ip_address'] = $ip_address;
		} else {
			$conditions ['created_by'] = $user->id;
		}

		$row->load ( $conditions );

		if ( $row->id > 0 )
		{
			$this->setError (JText::_("JCM_THIS_COMMENT_REPORTED_BY_YOU"));
			return false;
		}

		$data = array (
				'comment_id' 	=> $id,
				'reason_code' 	=> '',
				'reason_msg' 	=> $request->getString ('msg'),
				'created_by' 	=> $user->id,
				'created_time' 	=> JFactory::getDate()->toSql(),
				'ip_address' 	=> $ip_address,
				'guest_name'	=> $request->getString ('name'),
				'guest_email'	=> $request->getString ('email')
			);

		$row->bind ($data);

		if (! $row->check())
		{
			$this->setError ( $row->getError () );
			return false;
		}

		if (! $row->store())
		{
			$this->setError (JText::_("JCM_APPEAR_ERROR_WHEN_SAVING_YOUR_DATE_TRY_LATER"));
			return false;
		}

		return true;
	}

	public function remove ()
	{
		if ( $this->id < 1 )
		{
			$this->setError (JText::_("JCM_PERMISSION_DENIED"));
			return false;
		}

		$user = JFactory::getUser ();
		$ip_address = JLexCommentHelper::ip_address();

		$conditions = array ('comment_id' => $this->id);
		if ($user->guest)
		{
			$conditions ['ip_address'] = $ip_address;
		} else {
			$conditions ['created_by'] = $user->id;
		}

		$row = $this->getTable ('report', 'TableCm');
		$row->load ( $conditions );

		if  (!$row->delete ())
		{
			$this->setError (JText::_("JCM_PERMISSION_DENIED"));
			return false;
		}

		return true;
	}
}