<?php
/**
 * @package     JLex Comment
 * @version     1.0.0
 * @copyright   Copyright (C) 2013-2016 JLexArt Team (http://www.jlexart.com). All rights reserved.
 * @license     GNU/GPL or later
 * @author      JLexArt (support@jlexart.com)
 */

defined ( '_JEXEC' ) or die ();
jimport ("joomla.filesystem.file");

require_once dirname (__FILE__) . "/../libraries/adapter.php";

class JLexCommentModelImport extends JModelLegacy
{
    public function getApps ()
    {
    	$path = dirname (__FILE__) . "/import";
    	$plugins = JFolder::files ($path, ".php");

    	if (! count($plugins))
    	{
    		return null;
    	}

    	$list = array ();

    	foreach ($plugins as $k=>$plg)
    	{
    		$plg_path 		= $path . "/" . $plg;
    		$plg_part 		= explode (".", $plg);
    		$nameOfClass 	= "JLexCommentImport" . $plg_part [0];

    		require_once ( $plg_path );

    		$class = new $nameOfClass ();
    		$list [$k] = array (
    				"name" 	=> $class->name,
    				"count" => $class->getCountCm (),
    				"key" 	=> $plg_part [0]
    			);
    	}

    	return $list;
    }

    public function migrator ()
    {
        $app = JFactory::getApplication ();
        $plg = $app->input->getCmd ("migrator", "");

        $response = array();

        if ( !preg_match("/^[A-z0-9\_]+$/", $plg))
        {
            $this->setError ("Migrator not found.");
            return false;
        }

        $plg_path = dirname (__FILE__) . "/import/" . $plg . ".php";
        if (! JFile::exists($plg_path) )
        {
            $this->setError ("Migrator not found.");
            return false;
        }

        require_once ( $plg_path );
        $nameOfClass    = "JLexCommentImport" . $plg;

        $class = new $nameOfClass ();
        
        $getTotal = $app->input->getBool("gtotal", false);
        if ($getTotal)
        {
            $response["total"] = $class->getCountCm();
            if (!is_numeric($response["total"]) || !$response["total"])
            {
                $response["total"] = 0;
            }

            if ($response["total"]==0)
            {
                return $response;
            }
        }

        $offset = $app->input->getInt("offset", 0);
        if (!$class->import ($offset))
        {
            $response["success"] = -1;
        } else {
            $response["success"] = $class->total;
        }

        return $response;
    }

    public function upload()
    {
        $time = JFactory::getDate()->toUnix ();
        $file = array_key_exists('file', $_FILES) ? $_FILES['file'] : null;

        if (empty($file) || $file['size']==0 || $file['error']>0)
        {
            $this->setError(JText::_("JCM_SELECT_FILE_TO_IMPORT"));
            return false;
        }

        // safe filename
        $filename   = JFile::makeSafe($file['name']);
        $extension  = strtolower(JFile::getExt($filename) );
        $dest       = JPATH_ROOT . "/tmp/import_" . $time . '.tmp';

        if ($extension!='csv')
        {
            $this->setError(JText::_("JCM_ONLY_SUPPORT_CSV"));
            return false;
        }

        if(!JFile::upload($file['tmp_name'], $dest))
        {
            // throw an error message
            return false;
        }

        return $time;
    }

    public function getForm()
    {
        $path = dirname (__FILE__) . "/forms/import.xml";
        $form = JForm::getInstance("jcm_import", $path);

        $form->bind ( array("id"=>JFactory::getApplication()->input->getInt("id",0)) );

        return $form;
    }

    public function progress()
    {
        set_time_limit (0);
        require_once dirname(__FILE__)."/../libraries/parsecsv.lib.php";

        $app = JFactory::getApplication();
        $now = JFactory::getDate()->toSql();

        $id = $app->input->getInt('id', 0);
        $file = JPATH_ROOT . "/tmp/import_" . $id . '.tmp';

        if(!JFile::exists($file))
        {
            throw new Exception("File CSV not found or Session expired.", 404);
        }

        $csv = new parseCSV();
        if($app->input->getInt("heading", 0)==1)
        {
            $csv->heading = false;
        } else {
            $csv->heading = true;
        }
        
        $csv->auto($file);

        if(!count($csv->data))
        {
            throw new Exception(JText::_("JCM_NO_ROW_FOUND_IMPORT"), 500);
        }

        $import_success = 0;

        foreach($csv->data as $row)
        {
            $table = $this->getTable("comment", "tableCm");
            $row = array_values($row);

            $data = [
                    "obj_id"  => $app->input->getInt("object", 0),
                    "comment" => $row[$app->input->getString("comment", 0)],
                    "guest_name" => $row[$app->input->getString("author", 0)],
                    "created_time" => $row[$app->input->getInt("date", 0)],
                    "published" => $app->input->getInt("publish", 1),
                    "guest_email" => "import@unknown.com",
                    "created_by" => 0,
                    "ip_address" => "::1"
                ];

            $table->bind($data);

            // check datetime format
            try {
                $tz = JFactory::getDate($table->created_time)->toSql();
                $table->set("created_time", $tz);
            } catch(Exception $e){
                $table->set("created_time", $now);
            }

            if(!$table->check())
            {
                continue;
            }

            if($table->store())
            {
                $import_success+=1;
            }
        }

        return $import_success;
    }
}
