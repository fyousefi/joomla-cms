<?php
/**
 * @package     JLex Comment
 * @version     1.0.0
 * @copyright   Copyright (C) 2013-2016 JLexArt Team (http://www.jlexart.com). All rights reserved.
 * @license     GNU/GPL or later
 * @author      JLexArt (support@jlexart.com)
 */

defined ( '_JEXEC' ) or die ();

class JLexCommentHelper 
{
    static $config = null;

    public static function getConfig()
    {
        if(self::$config==null)
        {
            $params = JComponentHelper::getParams("com_jlexcomment");
            $user   = JFactory::getUser();

            $groups = $user->getAuthorisedGroups();
            $user_permissions = array (
                    "u_post_comment"    => array (1),
                    "u_reply_comment"   => array (1),
                    "u_upload_file" => array (1),
                    "u_mention" => array (1),
                    "u_vote_comment" => array (1),
                    "u_report_comment" => array (1),
                    "u_sub_comment" => array (1),
                    "u_auto_publish" => array (1),
                    "u_download_file" => array (1),


                    // admin
                    "u_edit_own_comment" => array (2),
                    "u_edit_any_comment" => array (4,7,8),
                    "u_del_own_comment" => array(2),
                    "u_del_any_comment" => array (7,8),
                    "u_state_any_comment" => array (5,7,8),
                    "u_feature_any_comment" => array (4,7,8),
                    "u_show_ip_addr" => array (4,5,7,8),
                    "u_show_author_email" => array (4,5,7,8),
                );

            // check blacklist
            $denied = false;

            if($params->get("blacklist",1)==1)
            {
                $db = JFactory::getDbo();
                $query = $db->getQuery(true);

                $orClauses=[];
                $ip=self::ip_address();
                $range=preg_replace('/[0-9]{1,3}$/', '*', $ip);

                // ipv6
                $orClauses[]='(method=3 AND '.$db->quote($ip).' LIKE REPLACE(method_value, "*", "%"))';
                // ipv4
                $orClauses[]='(method=3 AND method_value='.$db->quote($ip).')';
                $orClauses[]='(method=3 AND method_value='.$db->quote($range).')';
                if(!$user->guest) $orClauses[]='(method=1 AND method_value='.$db->quote($user->id).')';

                $query->select('*')
                      ->from('#__jlexcomment_blacklist')
                      ->where('('.implode(' OR ', $orClauses).')')
                      ->order('method ASC');

                $result = $db->setQuery($query,0,1)->loadObject();

                if($result)
                {
                    $msg = $result->method==1?JText::_("JCM_THIS_ACCOUNT_LIMITED"):JText::_("JCM_THIS_IP_ADDRESS_LIMITED");

                    if(!empty($result->reason))
                    {
                        $msg .= JText::sprintf("JCM_DETAIL_OF_REASON", $result->reason);
                    }

                    $denied = true;
                    $params->set("blocked", true);
                    $params->set("blocked_msg", $msg);
                }
            }

            foreach ( $user_permissions as $key => $value)
            {
                if ( $denied==true )
                {
                    $params->set ( $key, false);
                } else {
                    $permission = $params->def ($key, $value);

                    if ( count (array_intersect($groups, $permission)) > 0 )
                    {
                        $params->set ( $key, true);
                    } else {
                        $params->set ( $key, false);
                    }
                }
            }

            // special
            if($params->get('jcm_layout_media',1)==0)
            {
                $params->set('u_upload_file',false);
            }

            // caching
            $joomlaCacheTurnOn = self::joomlaCacheTurnOn();
            $params->set('joomla_cache', $joomlaCacheTurnOn);
            if($params->get('cache',1)==1 && $joomlaCacheTurnOn && $params->get('show_unpublished_cm',2)==2)
            {
                $params->set('show_unpublished_cm',3); // only registered
            }

            // cannot be zero
            if((int) $params->get('reply_in_cm',5)<1)
                $params->set('reply_in_cm', 5);

            if((int) $params->get('reply_in_cm_more',5)<1)
                $params->set('reply_in_cm_more', 5);

            self::$config = $params;
        }

        return self::$config;
    }

    public static function joomlaCacheTurnOn()
    {
        $config     = JFactory::getConfig();
        $caching    = $config->get('caching',0)>0?true:false;
        $plugin     = JPluginHelper::isEnabled('system', 'cache');

        if($caching || $plugin) return true;

        return false;
    }

    public static function ip_address ()
    {
        $config = JComponentHelper::getParams('com_jlexcomment');
        if($config->def('ip_address',1)==0) return '';

        $ipaddress = '';
        if (getenv('HTTP_CLIENT_IP'))
            $ipaddress = getenv('HTTP_CLIENT_IP');
        else if(getenv('HTTP_X_FORWARDED_FOR'))
            $ipaddress = getenv('HTTP_X_FORWARDED_FOR');
        else if(getenv('HTTP_X_FORWARDED'))
            $ipaddress = getenv('HTTP_X_FORWARDED');
        else if(getenv('HTTP_FORWARDED_FOR'))
            $ipaddress = getenv('HTTP_FORWARDED_FOR');
        else if(getenv('HTTP_FORWARDED'))
            $ipaddress = getenv('HTTP_FORWARDED');
        else if(getenv('REMOTE_ADDR'))
            $ipaddress = getenv('REMOTE_ADDR');
        else
            $ipaddress = 'UNKNOWN';

        return $ipaddress;
    }

    static $profile = null;

    public static function getProfile()
    {
        if ( self::$profile == null )
        {
            require_once (JPATH_ROOT . '/components/com_jlexcomment/libraries/profile.php');
            
            $params = self::getConfig ();
            self::$profile = new CommentProfile ( $params->def('profile_3rd', 'jlexcomment') );
        }

        return self::$profile;
    }

    /**
     * Convert string, array or object to JSON format.
     *
     * @param (string|array|object) $mix            
     * @return string JSON format
     */
    public static function mix2json($mix)
    {
        header("Content-Type:application/javascript");
        echo json_encode($mix);
        exit();
    }
    
    public static function encodeSub ( $com_name, $com_key )
    {
        $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charsLength = strlen($chars);
        $randomString = '';
        for ($i = 0; $i < 7; $i ++)
        {
            $randomString .= $chars[rand(0, $charsLength - 1)];
        }

        $key = array(
            'com_name' => $com_name,
            'com_key' => $com_key
        );

        $key = base64_encode(json_encode($key));
        $key = substr_replace($key, $randomString, 1, 0);

        return $key;
    }

    public static function decodeSub ($key)
    {
        $key = substr($key, 0, 1) . substr($key, 8);
        $data = json_decode ( base64_decode ($key ) );

        if ( empty($data) || ! isset($data->com_name) || ! isset($data->com_key) )
        {
            return false;
        }

        return $data;
    }


    public static function urls2html($string) 
    {
        // @refer: http://stackoverflow.com/questions/1960461/convert-plain-text-urls-into-html-hyperlinks-in-php
        $url = '~(?:(https?)://([^\s<]+)|(www\.[^\s<]+?\.[^\s<]+))(?<![\.,:])~i'; 
        $string = preg_replace($url, '<a href="$0" target="_blank" title="$0">$0</a>', $string);
        return nl2br($string);
    }

    /**
     * Format date time to short string.
     *
     * @param string $time            
     * @param string $now            
     * @return string Time formated
     */
    public static function formatTime($time, $now = null, $format = null, $timezone=true)
    {
        if ( $format!=null )
        {
            if ($timezone==true)
            {
                return JHtml::date ($time, $format);
            } else {
                $date = new JDate($time);
                return $date->format($format);
            }
        }

        if ($now==null)
        {
            $now = JFactory::getDate()->toUnix();
        } else {
            $now = JFactory::getDate($now)->toUnix();
        }
        
        $time = JFactory::getDate($time)->toUnix();
        $elstime = $now - $time;

        if ($elstime < 60) {
            $return = JText::_("JCM_RECENT");
        } elseif ($elstime < 3600) {
            $return = JText::sprintf("JCM_MINUTES_AGO", intval($elstime / 60));
        } elseif ($elstime < 24 * 3600) {
            $return = JText::sprintf("JCM_HOURS_AGO", intval($elstime / 3600));
        } elseif ($elstime < 24 * 3600 * 30) {
            $return = JText::sprintf("JCM_DAYS_AGO", intval($elstime / (3600 * 24)));
        } elseif ($elstime < 24 * 3600 * 30 * 12) {
            $return = JText::sprintf("JCM_MONTHS_AGO", intval($elstime / (3600 * 24 * 30)));
        } else {
            $return = JText::sprintf("JCM_YEARS_AGO", intval($elstime / (3600 * 24 * 30 * 12)));
        }
        return $return;
    }

    /**
     * Shorten string by word length
     *
     * @param string $text            
     * @param int $num            
     * @param string $end            
     * @return string
     */
    public static function subwords($text, $num = 10, $end = '')
    {
        $text = trim(strip_tags(nl2br($text)));
        $words = explode(' ', $text);
        if (count($words) <= $num)
            return $text;
        $subword = array_slice($words, 0, $num);
        $subword = implode(' ', $subword) . '...' . $end;
        return $subword;
    }

    public static function getUrl ($url, $method = false, $params = null)
    {
        if (! function_exists('curl_init'))
        {
            // ERROR: CURL library not found!');
            return false;
        }
        
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, $method);
        if ($method == true && isset($params)) {
            if (is_array($params))
            {
                $params = http_build_query($params, '', '&');
            }
            curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
        }
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Length: ' . strlen($params),
            'Cache-Control: no-store, no-cache, must-revalidate',
            "Expires: " . date("r")
        ));

        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        
        // curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
        
        $result = curl_exec($ch);
        curl_close($ch);
        
        return $result;
    }

    public static function mail2admin ($email, $quicklinks, $comments, $subject="")
    {
        jimport ("joomla.filesystem.file");
        $db     = JFactory::getDbo ();
        $config = self::getConfig ();
        $copy   = $config->get('email_to_owner',0);
        $configSys = JFactory::getConfig();

        $path   = trim($config->get('email_tpl_path',''),'/');
        if (preg_match('/^\s*$/', $path))
        {
            $path = 'administrator/components/com_jlexcomment/libraries/email_templates';
        }

        $path_admin = JPATH_ROOT.'/'.$path.'/admin.php';
        $path_user = JPATH_ROOT.'/'.$path.'/user.php';

        if (!JFile::exists($path_admin) && !JFile::exists($path_user))
        {
            return false;
        }

        $cid = array ();
        $prefix = 'jcm-quicklink--' . $db->getPrefix();
        foreach ($comments as $k=>&$comment)
        {
            $hashCode = 'hash=' . md5 ($prefix . '--' . $comment->id);

            $comment->url2publish   = JRoute::_('index.php?option=com_jlexcomment&task=item.next&id='.$comment->id.'&do=publish&' . $hashCode, false,($configSys->get('force_ssl',0)==2?1:-1));
            $comment->url2unpublish = JRoute::_('index.php?option=com_jlexcomment&task=item.next&id='.$comment->id.'&do=unpublish&' . $hashCode, false,($configSys->get('force_ssl',0)==2?1:-1));
            $comment->url2delete    = JRoute::_('index.php?option=com_jlexcomment&task=item.next&id='.$comment->id.'&do=delete&' . $hashCode, false,($configSys->get('force_ssl',0)==2?1:-1));
            $comment->url2featured  = JRoute::_('index.php?option=com_jlexcomment&task=item.next&id='.$comment->id.'&do=featured&' . $hashCode, false,($configSys->get('force_ssl',0)==2?1:-1));

            $cid[] = $comment->id;

            // send to owner
            if(JFile::exists($path_user) && $copy==1 && filter_var($comment->email_owner, FILTER_VALIDATE_EMAIL))
            {
                // bind to template
                $user_title = JText::_('JCM_YOU_HAVE_POST_A_COMMENT');
                ob_start ();
                include $path_user;
                $body = ob_get_contents ();
                ob_end_clean ();

                // sending
                $mailer     = JFactory::getMailer ();
                $mailer->isHTML( true );
                $mailer->setSubject( $user_title );
                $mailer->addRecipient( $comment->email_owner );
                $mailer->setBody( $body );
                $mailer->Send();
            }
        }

        // send to admin
        if(JFile::exists($path_admin) && $comment->email_owner!=$email)
        {
            // bind to template
            $title = jtext::_('JCM_NEW_COMMENT_ADD_YOUR_SITE');

            //$title = $subject;
            ob_start();
            include $path_admin;
            $body = ob_get_contents ();
            ob_end_clean ();

            // sending
            $mailer = JFactory::getMailer ();
            $mailer->isHTML ( true );
            $mailer->setSubject ( strip_tags($subject) );
            $mailer->addRecipient ( $email );
            $mailer->setBody ( $body );
            $mailer->Send ();
        }
        
        // update status
        $query = "UPDATE #__jlexcomment SET sent=1 WHERE id IN (". implode(",", $cid) . ")";
        $db->setQuery ($query)->execute();

        return true;
    }

    public static function loadThemes (&$view)
    {
        jimport ('joomla.filesystem.file');
        $themes = array();
        $config = self::getConfig();
        $app    = JFactory::getApplication();

        $theme_default = JPATH_ROOT . "/components/com_jlexcomment/themes/default";
        $theme_set = JPATH_ROOT . "/components/com_jlexcomment/themes/" . $config->get("theme", "default");

        if(JFolder::exists($theme_default))
        {
            $themes[]=$theme_default;
        }

        if($config->get("theme")!="default" && JFolder::exists($theme_set))
        {
            $themes[]=$theme_set;
            $css = JPATH_ROOT . "/components/com_jlexcomment/themes/" . $config->get("theme")."/style.css";
            if(is_file($css)) JFactory::getDocument()->addStyleSheet(JUri::root(true)."/components/com_jlexcomment/themes/" . $config->get("theme")."/style.css");
        }

        // adding from template pack
        $tpl_path = JPATH_ROOT . "/templates/" . $app->getTemplate() . "/html/com_jlexcomment/" . $config->get("theme","default");
        
        if(JFolder::exists($tpl_path)) $themes[]=$tpl_path;

        if(count($themes))
        {
            foreach($themes as $theme)
            {
                $view->addTemplatePath($theme);
            }
        }
    }

    /**
     * load modules based on its position
     *
     * @param string $position
     */
    public static function loadModules ($position)
    {
        jimport('joomla.application.module.helper');
        $modules = JModuleHelper::getModules($position);

        $html = ""; 
        if (count($modules))
        {
            foreach($modules as $module)
            {
                $html.= "\n" . JModuleHelper::renderModule($module);
            }
        }

        return $html;
    }

    public static function dispatcher($event, $data = array(), $type = 'embed')
    {
        JPluginHelper::importPlugin('jlexcomment');
        $results = JFactory::getApplication()->triggerEvent($event, $data);

        if (is_array($results) && count($results) > 0)
        {
            if ($type == 'embed')
            {
                $content = '';
                foreach ($results as $plg) {
                    $content.= (string) $plg;
                }
                return $content;
            }
        }

        return null;
    }

    static $objs = array();

    public static function getObjDetail($objId, $absolute=false)
    {
        if (array_key_exists($objId,self::$objs))
        {
            return self::$objs[$objId];
        }

        $db     = JFactory::getDbo();
        $query  = $db->getQuery(true);
        $config = JLexCommentHelper::getConfig();

        $result = new stdClass();
        $result->title  = 'Unknow';
        $result->url    = '#';

        $query->select('*')
              ->from('#__jlexcomment_obj')
              ->where('id=' . $objId);

        $row    = $db->setQuery($query)->loadObject();
        if(!$row)
        {
            self::$objs[$objId] = $result;
            return $result;
        }

        $result->title  = $row->title;
        $result->url    = $row->url;

        if ($config->def("cm_link",0)==1)
        {
            $object = CommentHelperAdmin::getObjDetail($row->com_name, $row->com_key,'', $row->url);  
            
            $result->title  = $object->title;
            $result->url    = str_replace(JUri::root(true), '', $object->url);
        }

        $result->url = ltrim($result->url, '/');
        $result->url = ( $absolute ? JUri::root() : (JUri::root(true).'/') ) . $result->url;

        return $result;
    }
}