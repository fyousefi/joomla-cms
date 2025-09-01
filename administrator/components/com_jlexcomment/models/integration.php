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
set_time_limit(0);

class JLexCommentModelIntegration extends JModelLegacy
{
    public function getApps ()
    {
        $app            = JFactory::getApplication ();
        $config         = JLexCommentHelper::getConfig ();

        $params = array (
                "cmd"  => "getApps",
                "key"   => "----"
            );

        $data = JLexCommentHelper::getUrl (JCM_SERVER, true, $params);
        if (! $data)
        {
            $app->enqueueMessage ("Your hosting/server must turn on cURL to use it.", "error");
            return false;
        }

        $data = json_decode($data);

        if (! $data || @$data->status==400)
        {
            $app->enqueueMessage ("Couldn't connect to JLexArt. Try again later.", "error");
            return false;
        }

        /*
        // Sample data
        $item = new stdClass ();
        $item->title = "JLexComment of K2";
        $item->description = "There is some description.";
        $item->id = 3;

        $item->type         = "plugin";
        $item->name         = "jlexreview";
        $item->folder       = "content";

        $item->img          = "http://www.adweek.com/files/imagecache/node-detail/news_article/facebook-app-iphone-hed-2013_2.jpeg";

        $item2 = new stdClass ();
        $item2->title = "JLexComment of VM";
        $item2->description = "There is some description.";
        $item2->id = 3;

        $item2->type         = "plugin";
        $item2->name         = "jlexcomment";
        $item2->folder       = "content";
        $item2->version       = "version";

        $data = array (
                $item,
                $item2
            );
        */

        $query = $this->_db->getQuery (true);
        foreach ($data as $k=>&$item)
        {
            if (!$item->direct)
            {
                $item->installed = false;
                continue;
            }

            $whereClauses = array (
                    $this->_db->quoteName ("type") . '=' . $this->_db->quote ($item->type),
                    $this->_db->quoteName ("element") . '=' . $this->_db->quote ($item->name)
                );

            if (isset($item->folder) && !empty($item->folder))
            {
                $whereClauses [] = $this->_db->quoteName ("folder") . '=' . $this->_db->quote ($item->folder);
            }

            $query->clear ();
            $query->select ("extension_id AS id,manifest_cache")
                  ->from ("#__extensions")
                  ->where ($whereClauses);

            $row = $this->_db->setQuery ($query)->loadObject ();

            if ($row)
            {
                $item->url_manager = "";

                if ($item->type=="plugin")
                {
                    $item->url_manager = JUri::base (true) . "/index.php?option=com_plugins&task=plugin.edit&extension_id=" . $row->id;
                } elseif ($item->type=="module")
                {
                    $item->url_manager = JUri::base (true) . "/index.php?option=com_modules&task=module.edit&id=" . $row->id;
                }

                $item->installed = true;
                $manifest = json_decode($row->manifest_cache);
                $item->update = version_compare($item->version, $manifest->version, ">") ? true : false;
            } else {
                $item->installed = false;
            }
        }

        return $data;
    }

    public function install ()
    {
        $app            = JFactory::getApplication ();
        $config         = JLexCommentHelper::getConfig ();
        $license_key    = $config->def("license_key","");
        if ( preg_match("/^\s*$/", $license_key) )
        {
            $url = JUri::base (true) . "/index.php?option=com_jlexcomment&view=settings";
            $app->redirect ($url, "You must fill your license key before.", "error");
            return false;
        }

        $id  = $app->input->getInt ("id", 0);

        if ($id < 1)
        {
            $this->setError ("Application not found." );
            return false;
        }

        $required = ini_get ( 'allow_url_fopen' ) && function_exists ( 'file_get_contents' );
        if (! $required)
        {
            $this->setError ("Your server don't support cURL.");
            return false;
        }

        $url = JCM_SERVER;
        $params = array (
                "cmd"  => "getApp",
                "key"   => $license_key,
                "id"    => $id
            );

        $url.= (strpos($url, "?")===false?"?":"&") . http_build_query ($params, "", "&");

        $ch = curl_init( $url );
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $file           = curl_exec($ch);
        $content_type   = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);

        curl_close($ch);


        if ( !preg_match("/zip/", $content_type) )
        {
            $this->setError ("Application not found or key incorrect.");
            return false;
        }

        if ($file===false)
        {
            $this->setError ("Application not found or key incorrect");
            return false;
        }

        // store and install file
        $config = JFactory::getConfig ();
        $tmp_dest = $config->get ( 'tmp_path' );
        $p_file = 'jlexcomment_' . time () . '.zip';
        $move = @file_put_contents ( $tmp_dest . '/' . $p_file, $file );
        if (! $move) {
            $this->setError ( 'Can\'t move file to tmp folder.' );
            return false;
        }
        $package = JInstallerHelper::unpack ( $tmp_dest . '/' . $p_file, true );
        
        $installer = JInstaller::getInstance ();
        if (! $installer->install ( $package ['dir'] )) {
            $this->setError ( 'Package error. Install failure !' );
            return false;
        }
        
        // Cleanup the install files.
        if (! is_file ( $package ['packagefile'] )) {
            $config = JFactory::getConfig ();
            $package ['packagefile'] = $config->get ( 'tmp_path' ) . '/' . $package ['packagefile'];
        }
        
        JInstallerHelper::cleanupInstall ( $package ['packagefile'], $package ['extractdir'] );
        return true;
    }
}
