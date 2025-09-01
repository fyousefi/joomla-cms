<?php
/**
 * @package     JLex Comment
 * @version     1.0.0
 * @copyright   Copyright (C) 2013-2016 JLexArt Team (http://www.jlexart.com). All rights reserved.
 * @license     GNU/GPL or later
 * @author      JLexArt (support@jlexart.com)
 */

defined ( '_JEXEC' ) or die;

jimport('joomla.filesystem.file');
set_time_limit(0);

class Com_JLexCommentInstallerScript
{
	protected $version 	= null;

	protected $release 	= null;

	protected $releases = array (
				"1.0.0",
				"1.1.0",
				"1.1.3",
				"1.1.5",
				"1.1.7",
				"1.2.0",
				"1.2.2",
				"1.2.3",
				"1.2.7",
				"1.3.3",
				"1.3.4",
				"1.3.7",
				"1.3.9",
				"1.4.0",
				"1.4.1",
				"1.4.7",
				"1.4.8",
				"1.4.9",
				"1.5.0",
				"1.5.3",
				"1.5.7",
				"1.5.9",
				"1.6.2",
				"2.0.0",
				"2.2.1",
				"2.2.5",
				"2.4.6",
				"2.4.7",
				"2.4.8",
				"2.5.1",
				"2.5.2",
				"2.7.4",
				"2.7.5",
				"2.8.1",
				"2.8.2",
				"2.9.5",
				"2.9.7",
				"3.0.1",
				"3.0.7",
				"3.1.7",
				"3.2.5",
				"3.2.6",
				"3.2.9",
				"3.3.8",
				"3.4.2"
			);


	/**
	 * This function to update sql upto new version
	 *
	 * @param string $file        	
	 */
	protected function _updateDbo($content)
	{
		$db = JFactory::getDbo();
		
		// create an array of queries from the sql file
		$queries = $this->_parseQuery($content);
		if(count($queries)==0) return 0;
		
		// Process each query in the $queries array (split out of sql file).
		foreach($queries as $query) 
		{
			$query = trim($query);
			
			if($query!='' && $query[0]!='#')
			{
				$db->setQuery( $query );
				
				if(!$db->execute())
				{
					JLog::add(JText::sprintf('JLIB_INSTALLER_ERROR_SQL_ERROR', $db->stderr(true)), JLog::WARNING, 'jerror');
					return false;
				}
			}
		}

		return count($queries);
	}

	protected function _parseQuery($sql)
	{
		$start = 0;
		$open = false;
		$char = '';
		$end = strlen($sql);
		$queries = array();
		
		for($i = 0; $i<$end; $i++)
		{
			$current = substr($sql, $i, 1);
			
			if(($current=='"' || $current=='\'')){
				$n = 2;
				
				while(substr($sql, $i-$n+1, 1)=='\\' && $n<$i) {
					$n++;
				}
				
				if ($n % 2 == 0) {
					if ($open) {
						if ($current == $char) {
							$open = false;
							$char = '';
						}
					} else {
						$open = true;
						$char = $current;
					}
				}
			}
			
			if(($current==';' && ! $open) || $i==$end-1)
			{
				$queries[] = substr($sql, $start, ($i-$start+1));
				$start = $i+1;
			}
		}
		
		return $queries;
	}

	public function getParam($param)
	{
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);

		$query->select("manifest_cache")
			  ->from("#__extensions")
			  ->where($db->quoteName("name")."=".$db->quote("com_jlexcomment"));
		
		$manifest_cache = $db->setQuery($query)->loadResult();

		if(empty($manifest_cache))
			return null;

		$manifest = json_decode($manifest_cache, true);
		
		return @$manifest[$param];
	}

	protected function _langnew($path, $version, $tag="en-GB")
	{
		$key = array_search($version, $this->releases);

		if($key===false || !isset($this->releases[$key+1])) return;

		$matches  = array();
		$version  = preg_quote(";v" . $this->releases[$key+1]);
		$end 	  = preg_quote(";end");

		if(!JFile::exists($path)) return;

		preg_match("/{$version}(.*){$end}/msi", file_get_contents($path), $matches);

		if(!isset($matches[1]))
		{
			$this->_langnew($path, $this->releases[$key+1], $tag);
			return;
		}

		$lines = explode(PHP_EOL, $matches[1]);
		$newlines = array();

		if(!count($lines)) return;

		foreach($lines as $line)
		{
			if(!empty($line)) $newlines[] = $line;
		}

		if(!count($newlines)) return;

		$data =  PHP_EOL . ";v" . $this->releases[$key+1] . PHP_EOL . implode(PHP_EOL, $newlines);
		
		$path2file = JPATH_ADMINISTRATOR . "/language/{$tag}/{$tag}.com_jlexcomment.ini";

		@file_put_contents($path2file, $data, FILE_APPEND | LOCK_EX);
	}
	
	public function create_params($src)
	{
		jimport('joomla.form.form');
		$db = JFactory::getDBO();

		// settings
		$path = $src . "/admin/models/forms/settings.xml";
        $form = JForm::getInstance("jcm_settings", $path);
        $params = array();

        foreach($form->getFieldsets() as $fieldset)
        {
        	//print
        	foreach ($form->getFieldset($fieldset->name) as $field)
        	{
        		$name 	= $field->getAttribute("name");
        		$value 	= $field->getAttribute("default");
        		if(empty($name))
        		{
        			continue;
        		}
        		$params[$name] = $value=="now" ? "": $value;
        	}
        }

        // user group
        $query = $db->getQuery(true);
        $query->select("id")->from("#__usergroups");
        $groups = $db->setQuery($query)->loadColumn();
        $groups_admin = array();

        if(in_array(7, $groups)) $groups_admin[] = 7;
        if(in_array(8, $groups)) $groups_admin[] = 8;

        // permission
        $path = $src . "/admin/models/forms/permission.xml";
        $permission = JForm::getInstance("jcm_permission", $path, array('control' => ''), false, '/permissions');

        foreach($permission->getFieldset("post") as $field)
    	{
    		$name = $field->getAttribute("name");
    		$params[$name] = $groups;
    	}

    	foreach($permission->getFieldset("administration") as $field)
    	{
    		$name = $field->getAttribute("name");
    		$params[$name] = $groups_admin;
    	}

    	// reactions
    	$params['jcm_reaction_data'] = json_encode(array(
    			array(
    					'label' => 'Like',
    					'icon'  => 'media/jcm/reaction/like.svg',
    					'color' => '6786c5',
    					'id'	=> 2
    				),
    			array(
    					'label' => 'Haha',
    					'icon'  => 'media/jcm/reaction/haha.svg',
    					'color' => 'fed871',
    					'id'	=> 3
    				),
    			array(
    					'label' => 'Love',
    					'icon'  => 'media/jcm/reaction/love.svg',
    					'color' => 'f05169',
    					'id'	=> 4
    				),
    			array(
    					'label' => 'Wow',
    					'icon'  => 'media/jcm/reaction/wow.svg',
    					'color' => 'fed871',
    					'id'	=> 5
    				),
    			array(
    					'label' => 'Sad',
    					'icon'  => 'media/jcm/reaction/sad.svg',
    					'color' => 'fed871',
    					'id'	=> 6
    				),
    			array(
    					'label' => 'Angry',
    					'icon'  => 'media/jcm/reaction/angry.svg',
    					'color' => 'e3253a',
    					'id'	=> 7
    				)
    		));

    	$params = json_encode($params);

    	$query->clear()
    		  ->update("#__extensions")
    		  ->set("params=".$db->quote($params))
    		  ->where([
    		  		$db->quoteName("type")."=".$db->quote("component"),
    		  		$db->quoteName("element")."=".$db->quote("com_jlexcomment")
    		  	]);

    	$db->setQuery($query)->execute();

    	return true;
	}

	public function update_params()
	{
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);

		$query->select("params")
			  ->from("#__extensions")
			  ->where([
    		  		$db->quoteName("type")."=".$db->quote("component"),
    		  		$db->quoteName("element")."=".$db->quote("com_jlexcomment")
    		  	]);
    	$params = $db->setQuery($query)->loadResult();

    	if(!$params) return;

		$params = json_decode($params,true);
		if(!$params) $params = array();

		// reactions
    	$params['jcm_reaction_data'] = json_encode(array(
    			array(
    					'label' => 'Like',
    					'icon'  => 'media/jcm/reaction/like.svg',
    					'color' => '6786c5',
    					'id'	=> 2
    				),
    			array(
    					'label' => 'Haha',
    					'icon'  => 'media/jcm/reaction/haha.svg',
    					'color' => 'fed871',
    					'id'	=> 3
    				),
    			array(
    					'label' => 'Love',
    					'icon'  => 'media/jcm/reaction/love.svg',
    					'color' => 'f05169',
    					'id'	=> 4
    				),
    			array(
    					'label' => 'Wow',
    					'icon'  => 'media/jcm/reaction/wow.svg',
    					'color' => 'fed871',
    					'id'	=> 5
    				),
    			array(
    					'label' => 'Sad',
    					'icon'  => 'media/jcm/reaction/sad.svg',
    					'color' => 'fed871',
    					'id'	=> 6
    				),
    			array(
    					'label' => 'Angry',
    					'icon'  => 'media/jcm/reaction/angry.svg',
    					'color' => 'e3253a',
    					'id'	=> 7
    				)
    		));

    	$params = json_encode($params);

    	$query->clear()
    		  ->update("#__extensions")
    		  ->set("params=".$db->quote($params))
    		  ->where([
    		  		$db->quoteName("type")."=".$db->quote("component"),
    		  		$db->quoteName("element")."=".$db->quote("com_jlexcomment")
    		  	]);

    	$db->setQuery($query)->execute();

    	return true;
	}

	public function create_sticker($src)
	{
		$date = JFactory::getDate()->toSql();
		$user = JFactory::getUser();

		$sql  = $src."/admin/update/data.mysql.sql";
		$content = file_get_contents($sql);

		$content = str_replace(array("{{userid}}", "{{date}}"), array($user->id, $date), $content);
		$this->_updateDbo($content);
	}
	
	public function preflight($type, $parent)
	{
		$this->release = $parent->getManifest()->version;
		$this->version = $this->getParam('version');

		$path = $parent->getParent()->getPath('source') . "/admin/languages/en-GB.com_jlexcomment.ini";

		if($type=='update')
		{
			$this->_langnew($path, $this->version);

			// remove language file
			JFile::delete($path);
		} else {
			// copy to language folder
			$dest = JPATH_ADMINISTRATOR . "/language/en-GB/en-GB.com_jlexcomment.ini";
			JFile::copy($path, $dest);
		}
	}
	
	/**
	 * method to run after an install/update/uninstall method
	 *
	 * @return void
	 */
	public function postflight($type, $parent)
	{
		$src = $parent->getParent()->getPath('source');

		if($type=="install")
		{
			$this->create_params($src);
			$this->create_sticker($src);
		} else {
			// update database - params for version bellow 1.3.3
			$ls = [
				"1.3.3",
				"1.3.9",
				"1.5.7",
				"1.6.2",
				"2.0.0",
				"2.4.8",
				"2.5.1",
				"2.9.7",
				"3.2.9"
			];

			foreach($ls as $v)
			{
				if(version_compare($this->version, $v, '<'))
				{
					$updateDbo = $src . "/admin/update/update".str_replace(".", "", $v).".mysql.sql";
					if(file_exists($updateDbo))
					{
						$content = file_get_contents($updateDbo);
						$this->_updateDbo($content);
					}

					$this->update_params();
				}
			}

			$app = JFactory::getApplication();
			$app->enqueueMessage("...Update success to " . $this->release);

			$session = JFactory::getSession();
			$session->set("jcm_versions", null);
		}
	}
	
	/**
	 * Redirect to index page
	 *
	 * @param object $parent        	
	 */
	public function install($parent)
	{
		$app = JFactory::getApplication();
		$app->enqueueMessage("Thank You for Using JLex Comment Extension!");

		// $parent is the class calling this method
		$url = JUri::base(true).'/index.php?option=com_jlexcomment';

		$parent->getParent()->setRedirectURL($url);
		return;
	}
}
        