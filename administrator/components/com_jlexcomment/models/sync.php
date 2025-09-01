<?php
/**
 * @package     JLex Comment
 * @version     1.0.0
 * @copyright   Copyright (C) 2013-2016 JLexArt Team (http://www.jlexart.com). All rights reserved.
 * @license     GNU/GPL or later
 * @author      JLexArt (support@jlexart.com)
 */

defined ( '_JEXEC' ) or die;

class JLexCommentModelSync extends JModelLegacy
{
	public $offset = 0;

	public $limit = 20;

	public $total = 0;

	public $sort = 's.created_time';

	public $sort_dir = 'DESC';

	public function getCallbacks()
	{
		$app 	= JFactory::getApplication();
		$prefix = "com_jlexcomment.sync.";

		$query 	= $this->_db->getQuery(true);
		$query->select("SQL_CALC_FOUND_ROWS s.*")
			  ->from("#__jlexcomment_sync AS s");

		// sorting
		$sortAllows = array (
			's.object',
			's.created_time',
			's.modified_time',
			's.published'
		);
		
		
		if($this->sort_dir!='ASC'&&$this->sort_dir!='DESC') $this->sort_dir = 'DESC';

		if(!in_array($this->sort, $sortAllows)) $this->sort = 's.created_time';

		$query->order($this->sort.' '.$this->sort_dir);
		
		$items = $this->_db->setQuery($query, $this->offset, $this->limit )->loadObjectList();
		$this->total = $this->_db->setQuery("SELECT FOUND_ROWS()")->loadResult();

		
		if(!$items) return null;

		foreach ($items as $k=>$item)
		{
			$items[$k]->url2edit = JUri::base(true)."/index.php?option=com_jlexcomment&view=sync&layout=form&id=".$item->id;
		}
		
		return $items;
	}

	public function getPagination()
	{
		jimport('joomla.html.pagination');
		return new JPagination( $this->total, $this->offset, $this->limit );
	}

	public function getCallback($id)
	{
		$app 	= JFactory::getApplication();
		$query 	= $this->_db->getQuery(true);
		$query->select("*")
			  ->from("#__jlexcomment_sync")
			  ->where("id=".$this->_db->quote($id));
		
		$item = $this->_db->setQuery($query)->loadObject();
		
		return $item;
	}

	public function getForm()
	{
		$path 	= dirname (__FILE__) . "/forms/sync.xml";
		$form 	= JForm::getInstance("jcm_sync", $path, array('control' => 'jform'));

		$form->addFieldPath (dirname (__FILE__) . "/fields");

		return $form;
	}

	public function save()
	{
		$app 	= JFactory::getApplication();
		$row 	= $this->getTable('sync', 'TableCm');
		
		$user 	= JFactory::getUser();
		$now 	= JFactory::getDate()->toSql();
		$query  = $this->_db->getQuery(true);

		$form 	= $this->getForm();
		
		$data 	= array_key_exists("jform", $_POST) ? $_POST["jform"] : array();

		$id = array_key_exists("id", $data) && preg_match('/^[1-9][0-9]*$/', $data['id']) ? $data['id']:0;

		$data['object'] = array_key_exists("object", $data) && preg_match('/^[A-z0-9]{2,}$/', $data['object']) ? $data['object']:'';

		if(empty($data['object']))
		{
			$this->setError(jtext::_('JCM_OBJECT_VALID_CHARACTERS'));
			return false;
		}

		$data['object'] = strtolower($data['object']);
		$row->load($id);

		if(!$row->id)
		{
			// check if this object is existing
			$query->clear()
				  ->select('COUNT(*)')
				  ->from('#__jlexcomment_sync')
				  ->where('object='.$this->_db->quote($data['object']));

			$count = $this->_db->setQuery($query)->loadResult();

			if($count>0)
			{
				$this->setError(jtext::sprintf('JCM_SYNC_ENTRY_IS_FOUND', $data['object']));
				return false;
			}
		}

		$data 	= $form->filter($data);
		$return = $form->validate($data);

		if($return===false)
		{
			// Get the validation messages.
			$errors = $form->getErrors();

			foreach ($errors as $error)
			{
				if ($error instanceof Exception)
				{
					$this->setError($error->getMessage());
				}
				else
				{
					$this->setError($error);
				}
			}

        	return false;
		}

		if(empty($row->id))
		{
			$data['created_time'] = $now;
			$data['created_by'] = $user->id;
		} else {
			$data['modified_time'] = $now;
		}

		$row->bind($data);

		if(!$row->store())
		{
			$this->setError(jtext::_('JCM_AN_ERROR_WHILE_SAVING_DATA'));
			return false;
		}

		return true;
	}

	public function listCmd($action, $cid)
	{
		$query = $this->_db->getQuery(true);

		switch ($action)
		{
			case 'publish':
			case 'unpublish':
				$query->update('#__jlexcomment_sync')
					  ->set('published='.($action=='publish'?1:0))
					  ->where('id IN('.implode(',', $cid).')');

				$this->_db->setQuery($query)->execute();
				break;
		}

		return true;
	}

	public function sync()
	{
		$query = $this->_db->getQuery(true);
		$query->select('SQL_CALC_FOUND_ROWS *')
			  ->from('#__jlexcomment_obj')
			  ->order('id ASC');

		$rows = $this->_db->setQuery($query, $this->offset, $this->limit)->loadObjectList();

		$this->total = $this->_db->setQuery("SELECT FOUND_ROWS()")->loadResult();

		if($rows)
		{
			foreach($rows as $row)
			{
				$sync = CommentHelperAdmin::getSync($row->com_name, $row->com_key);
        		$sync->action("entry_details");
			}
		}

		$result = new stdClass();
		$result->total 	= intval($this->total);
		$result->offset = $this->offset;
		$result->limit 	= $this->limit;

		return $result;
	}
}