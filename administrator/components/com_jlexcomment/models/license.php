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

class JLexCommentModelLicense extends JModelLegacy
{
    public function activate ()
    {
        $app = JFactory::getApplication();
        $key = $app->input->getString("api_key", "");

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

        // get latest version from JLexArt.com
        $url = JCM_SERVER . (strpos(JCM_SERVER, "?")===false?"?":"&") . "cmd=check_license&key=" . $key;
            $url.= "&version=" . $manifest->version;

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
                $this->setError ("Couldn't connect to JLexArt.com. Please contact us to solve this issue.");
                return false;
            } else {
                if ($data->status!=200)
                {
                    $this->setError ($data->error);
                    return false;
                }

                // key correct. Activate now.
                $path = JPATH_ADMINISTRATOR . "/components/com_jlexcomment/views/license/tmpl/form.php";
                $content = "";
                if (! JFile::write ($path, $content))
                {
                    $this->setError ("The license record can't in your site. Please contact us to solve this issue.");
                    return false;
                }
            }
        }

        return true;
    }
}
