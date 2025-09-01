<?php
/**
 * @package     JLex Comment
 * @version     1.0.0
 * @copyright   Copyright (C) 2013-2016 JLexArt Team (http://www.jlexart.com). All rights reserved.
 * @license     GNU/GPL or later
 * @author      JLexArt (support@jlexart.com)
 */

defined ( '_JEXEC' ) or die;

if(function_exists('ini_set'))
    ini_set("pcre.jit", "0"); // PHP 7

require_once dirname(__FILE__).'/bootstrap.php';

class JLexCommentLoader
{
    public static function init($component, $id, $title = "", $url = null)
    {
        // AMP
        /**
        $frame_url = '<CUSTOM-DOMAIN>/index.php?option=com_jlexcomment';
        $frame_url_query = http_build_query([
            'k' => JLexCommentHelper::encodeSub($component, $id),
            'title' => $title,
            'url' => urlencode($url)
        ]);

        $frame_url.= (preg_match('/\?/', $frame_url)?'&':'?').$frame_url_query;

        $amp = '<amp-iframe width="500" height="300"
                    sandbox="allow-scripts allow-same-origin allow-modals"
                    layout="responsive"
                    frameborder="0"
                    resizable
                    src="'.$frame_url.'"><div overflow tabindex=0 role=button aria-label="Read more">Read more!</div></amp-iframe>';

        return $amp;
        **/

        require_once dirname(__FILE__)."/controller.php";
        $controller = new JLexCommentController();

        $controller->addModelPath(dirname(__FILE__) . "/models" , "JLexComment");
        $controller->addViewPath(dirname(__FILE__) . "/views");

        $controller->setParam("locked", true);
        $controller->setParam("com_name", $component);
        $controller->setParam("com_key", $id);
        $controller->setParam("com_title", $title);
        $controller->setParam("com_url", $url);

        return $controller->display();
    }

    public static function activities($uid)
    {
        require_once dirname(__FILE__) . "/controllers/user.php";
        $controller = new JLexCommentControllerUser();

        $controller->addModelPath (dirname(__FILE__) . "/models" , "JLexComment");
        $controller->addViewPath (dirname(__FILE__) . "/views");

        $controller->set ("uid", $uid);

        return $controller->activity();
    }

    protected static $entry_counts = array ();

    protected static $language = false;

    public static function count_cm($component, $id, $url=null ,$html=true)
    {
        $db = JFactory::getDbo();
        $config = JLexCommentHelper::getConfig ();

        $key = $component . "." . $id;
        $key2 = $key.($html?".html":"");

        if(array_key_exists($key2, self::$entry_counts))
        {
            return self::$entry_counts[$key2];
        }

        $whereClauses = array(
                $db->quoteName('com_name')."=".$db->quote($component),
                $db->quoteName('com_key')."=".$db->quote($id),
                $db->quoteName('published')."=".$db->quote(1)
            );

        $query = $db->getQuery(true);
        $query->select(($config->get('count_cm',0)==0 ? $db->quoteName("cm_i_count_active"):$db->quoteName("cm_count_active")) . " AS cm_count" )
              ->select("com_name,com_key,title,url")
              ->from('#__jlexcomment_obj')
              ->where($whereClauses);

        $result = $db->setQuery($query)->loadObject();

        if (!$result)
        {
            $result = new stdClass();
            $result->cm_count = 0;
            $result->url = $url;
        } else {
            $result->url = JUri::root(true) . "/" . trim($result->url,'/');
        }
        
        if($config->get("cm_link",0)==1)
        {
            if(method_exists('CommentHelperAdmin','getObjDetail'))
            {
                $up = CommentHelperAdmin::getObjDetail($component, $id, "" , $result->url);
                if($up) $result->url = $up->url;
            } else {
                $result->url = CommentHelperAdmin::getRelativeUrl($component, $id, $result->url);
            }
        }

        self::$entry_counts[$key] = $result;

        if($html)
        {
            if(!self::$language)
            {
                self::$language = true;

                $lang = JFactory::getLanguage();
                $extension = "com_jlexcomment";
                $lang->load($extension, JPATH_ADMINISTRATOR);
            }

            $icon = '<img src="'.JUri::base(true).'/media/jcm/sys/icon.png" />';
            $prefix = $result->cm_count>0 ? ($result->cm_count>1?jtext::sprintf("JCM_TOTAL_COMMENTS",$result->cm_count):jtext::_("JCM_ONE_COMMENT")) : jtext::_("JCM_WRITE_A_COMMENT");
            $url = !empty($result->url)?$result->url:$url;

            if(!empty($url))
            {
                $html = '<a class="jcm-count-cm" href="'.$url.'#comment">'. $icon .' '. $prefix .'</a>';
            } else {
                $html = '<span>'. $icon .' '. $prefix .'</span>';
            }

            self::$entry_counts[$key.".html"] = $html;

            return $html;
        }

        return $result;
    }
}