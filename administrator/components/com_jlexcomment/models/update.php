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

class JLexCommentModelUpdate extends JModelLegacy
{
    public function check_version ()
    {
        $session = JFactory::getSession ();
        $versions = $session->get ("jcm_versions", null);

        if ($versions)
        {
            return $versions;
        }

        // get current version
        $whereClauses = array (
                $this->_db->quoteName ("type") . '=' . $this->_db->quote ("component"),
                $this->_db->quoteName ("element") . '=' . $this->_db->quote ("com_jlexcomment")
            );

        $query = $this->_db->getQuery (true);
        $query->select ("manifest_cache")
              ->from ("#__extensions")
              ->where ( $whereClauses );

        $manifest = $this->_db->setQuery ($query)->loadResult ();
        $manifest = json_decode($manifest);

        $versions ['local'] = $manifest->version;

        // get latest version from JLexArt.com
        $url = JCM_SERVER . (strpos(JCM_SERVER, "?")===false?"?":"&") . "cmd=get_latest_version";

        $required = ini_get ( 'allow_url_fopen' ) && function_exists ( 'file_get_contents' );
        if (! $required)
        {
            $versions ['server'] = $versions ['local'];
            $versions ['error']  = "Couldn't connect to JLexArt.com | <b>allow_url_fopen</b> in your hosting is disabled.";
        } else {
            $data = @file_get_contents($url);
            $data = json_decode($data);

            if (!$data)
            {
                $versions ['server']    = $versions ['local'];
                $versions ['error']     = "Couldn't connect to JLexArt.com";
            } else {
                $versions ['server']    = $data->version;
                $versions ['released']  = $data->date;
            }
        }

        // store this variable to session
        $session->set ("jcm_versions", $versions);

        return $versions;
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

        $required = ini_get ( 'allow_url_fopen' ) && function_exists ( 'file_get_contents' );
        if (! $required)
        {
            $this->setError ("Your server don't support cURL.");
            return false;
        }

        $versions = $this->check_version ();
        if (version_compare($versions["server"],$versions["local"],"<="))
        {
            $this->setError (JText::_("JCM_YOU_ARE_USING_LATEST_VERSION"));
            return false;
        }

        $url = JCM_SERVER;
        $params = array (
                "cmd"  => "upgrade",
                "key"   => $license_key,
                "version" => $versions ["local"]
            );

        $url.= (strpos($url, "?")===false?"?":"&") . http_build_query ($params, "", "&");

        $header = get_headers ($url,1);
        if ( !preg_match("/zip/", $header["Content-Type"]) )
        {
            $this->setError (JText::_("JCM_APP_NOT_FOUND_OR_KEY_INCORRECT"));
            return false;
        }

        $file = file_get_contents ($url);

        if ($file===false)
        {
            $this->setError (JText::_("JCM_APP_NOT_FOUND_OR_KEY_INCORRECT"));
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
        
        // clear session
        $session = JFactory::getSession ();
        $session->set ("jcm_versions", null);

        return true;
    }
}
