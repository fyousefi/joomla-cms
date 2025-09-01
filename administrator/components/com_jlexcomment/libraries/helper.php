<?php
/**
 * @package     JLex Comment
 * @version     1.0.0
 * @copyright   Copyright (C) 2013-2016 JLexArt Team (http://www.jlexart.com). All rights reserved.
 * @license     GNU/GPL or later
 * @author      JLexArt (support@jlexart.com)
 */

defined ( '_JEXEC' ) or die ();

class CommentHelperAdmin 
{
    public static function sidebar ($view)
    {
        if (JE_JVERSION=="J4") return;
        $listViews = array (
                'dashboard' => array(
                        "text" => JText::_("JCM_MN_DASHBOARD"),
                        "icon" => "dashboard"
                    ),
                'items'     => array(
                        "text" => JText::_("JCM_MN_ENTRIES"),
                        "icon" => "item"
                    ),
                'comments'  => array(
                        "text" => JText::_("JCM_MN_COMMENTS"),
                        "icon" => "comments"
                    ),
                'settings'  => array(
                        "text" => JText::_("JCM_MN_SETTINGS"),
                        "icon" => "config"
                    ),
                'emoji'     => array(
                        "text" => JText::_("JCM_MN_EMOJI"),
                        "icon" => "smile"
                    ),
                'stickergroup'   => array(
                        "text" => JText::_("JCM_MN_STICKER_GROUP"),
                        "icon" => "box"
                    ),
                'sticker'   => array(
                        "text" => JText::_("JCM_MN_STICKER"),
                        "icon" => "sticker"
                    ),
                'subscription' => array(
                        "text" => JText::_("JCM_MN_SUBSCRIPTION"),
                        "icon" => "bell"
                    ),
                'blacklist' => array(
                        "text" => JText::_("JCM_MN_BLACKLIST"),
                        "icon" => "warning"
                    ),
                'import'    => array(
                        "text" => JText::_("JCM_MN_IMPORT"),
                        "icon" => "download"
                    ),
                'reporting' => array(
                        "text" => JText::_("JCM_MN_REPORTING"),
                        "icon" => "flag"
                    ),
                'roles'     => array(
                        "text" => JText::_("JCM_MN_ROLES"),
                        "icon" => "tag"
                    ),
                'replacer'  => array(
                        "text" => JText::_("JCM_MN_REPLACER"),
                        "icon" => "replace"
                    ),
                'style'  => array(
                        "text" => JText::_("JCM_STYLE"),
                        "icon" => "style"
                    ),
                'users'     => array(
                        "text" => JText::_("JCM_MN_USERS"),
                        "icon" => "users"
                    ),
                'mailq'     => array(
                        "text" => JText::_("JCM_MN_MAILQ"),
                        "icon" => "email"
                    ),
                'integration'     => array(
                        "text" => JText::_("JCM_MN_INTEGRATION"),
                        "icon" => "integration"
                    ),
                'sync'     => array(
                        "text" => JText::_("JCM_MN_SYNC"),
                        "icon" => "replace"
                    )
            );

        $html = "\n<div id=\"sidebar\" class=\"sidebar\">";
            $html.= "<div class=\"sidebar-nav\">";
                $html.= "<ul id=\"submenu\" class=\"nav nav-list\">";

                foreach ($listViews as $k=>$viewName)
                {
                    /*JHtmlSidebar::addEntry(
                        $viewName,
                        'index.php?option=com_jlexcomment&view=' . $k,
                        $view==$k
                    );*/
                    $html.= "<li". ($view==$k?' class="active"':'') .">";
                        $html.= "<a href=\"index.php?option=com_jlexcomment&view={$k}\">";
                            $html.= "<i class=\"jcm-icon-". $viewName["icon"] ."\"></i>";
                            $html.= "<span>" . $viewName["text"] ."</span>";
                        $html.= "</a>";
                    $html.= "</li>";
                }

                    // extra link
                    $html.= "<li class=\"jcm-extra-urls\">";
                        $html.= "<span class=\"hasTip\" title=\"JLexArt.COM\"><a href=\"http://www.jlexart.com\" target=\"_blank\"><i class=\"jcm-icon-globe\"></i></a></span>";
                        $html.= "<span class=\"hasTip\" title=\"Facebook\"><a href=\"https://www.facebook.com/JLexArt\" target=\"_blank\"><i class=\"jcm-icon-facebook\"></i></a></span>";
                        $html.= "<span class=\"hasTip\" title=\"Twitter\"><a href=\"https://twitter.com/jlexartdotcom\" target=\"_blank\"><i class=\"jcm-icon-twitter\"></i></a></span>";
                        $html.= "<span class=\"hasTip\" title=\"Youtube\"><a href=\"https://www.youtube.com/user/JLexTeam\" target=\"_blank\"><i class=\"jcm-icon-youtube-play\"></i></a></span>";
                        $html.= "<span class=\"hasTip\" title=\"Documentation\"><a href=\"http://redirect.jlexart.com?alias=jcm_doc\" target=\"_blank\"><i class=\"jcm-icon-book\"></i></a></span>";
                        $html.= "<span class=\"hasTip\" title=\"JED\"><a href=\"http://redirect.jlexart.com?alias=jcm_jed\" target=\"_blank\"><i class=\"jcm-icon-joomla\"></i></a></span>";
                    $html.= "</li>";

                $html.= "</ul>";
            $html.= "</div>";
        $html.= "</div>";

        //return JHtmlSidebar::render();

        return $html;
    }

    static $sync = null;

    public static function getSync($object, $object_id)
    {
        if(self::$sync==null)
        {
            require_once dirname(__FILE__).'/sync.php';
            self::$sync = new JLexCommentSync();
        }

        self::$sync->set($object, $object_id);

        return self::$sync;
    }

    public static function lis(){}

    public static function update($id, $type="item")
    {
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);

        if($type=="item")
        {
            $query->select("COUNT(*) AS cm_count")
                  ->select("SUM(IF(published=1,1,0)) AS cm_count_active")
                  ->select("SUM(IF(parent_id=0,1,0)) AS cm_i_count")
                  ->select("SUM(IF(parent_id=0 AND published=1,1,0)) AS cm_i_count_active")
                  ->from("#__jlexcomment")
                  ->where("obj_id=".$db->quote($id));

            $result = $db->setQuery($query)->loadObject();

            $cm_count           = 0;
            $cm_count_active    = 0;
            $cm_i_count         = 0;
            $cm_i_count_active  = 0;
            $latest_update      = 0;

            if ($result)
            {
                $cm_count = intval($result->cm_count);
                $cm_count_active = intval($result->cm_count_active);
                $cm_i_count = intval($result->cm_i_count);
                $cm_i_count_active = intval($result->cm_i_count_active);
            }

            $query->clear()
                  ->select("MAX(created_time)")
                  ->from("#__jlexcomment")
                  ->where([
                        "obj_id=".$db->quote($id),
                        "published=1"
                    ]);
            $latest_update = $db->setQuery($query)->loadResult();
            $latest_update = !$latest_update?0:JFactory::getDate($latest_update)->toUnix();

            $query->clear()
                  ->update("#__jlexcomment_obj")
                  ->set([
                        "cm_count=".$db->quote($cm_count),
                        "cm_count_active=".$db->quote($cm_count_active),
                        "cm_i_count=".$db->quote($cm_i_count),
                        "cm_i_count_active=".$db->quote($cm_i_count_active),
                        "latest_update=".$db->quote($latest_update)
                    ])
                  ->where("id=".$db->quote($id));

            $db->setQuery($query)->execute();

            // sync
            $query->clear()
                  ->select('com_name, com_key')
                  ->from('#__jlexcomment_obj')
                  ->where('id='.$db->quote($id));

            $entry = $db->setQuery($query)->loadObject();
            if($entry)
            {
                $sync = self::getSync($entry->com_name, $entry->com_key);
                $sync->action("entry_updated", $cm_i_count, $cm_count);
            }
        } else {
            $query = "SELECT COUNT(*) AS child_count, SUM(IF(published=1,1,0)) AS child_count_active FROM #__jlexcomment WHERE parent_id=" . $id;
            $result = $db->setQuery($query)->loadObject();

            $child_count = 0;
            $child_count_active = 0;

            if ($result)
            {
                $child_count = intval($result->child_count);
                $child_count_active = intval($result->child_count_active);
            }

            $query = "UPDATE #__jlexcomment SET child_count=".$db->quote($child_count).", child_count_active=".$db->quote($child_count_active)." WHERE id=" . $id;

            $db->setQuery($query)->execute();
        }
    }


    static $router = null;

    public static function getRelativeUrl($component, $id, $url)
    {
        if ( self::$router == null )
        {
            require_once dirname(__FILE__) . "/router/router.php";
            self::$router = new JLexCommentRouter();
        }

        return self::$router->getUrl($component, $id, $url);
    }


    static $objs = array();

    public static function getObjDetail($component, $id, $title='', $url='')
    {
        if ( self::$router == null )
        {
            require_once dirname(__FILE__) . "/router/router.php";
            self::$router = new JLexCommentRouter();
        }

        $key = $component . '__' . $id;
        if (array_key_exists($key,self::$objs))
        {
            return self::$objs[$key];
        }

        $row = self::$router->getDetail($component, $id, $title, $url);
        self::$objs[$key] = clone $row;

        return $row;
    }

    public static function fileDelete($id)
    {
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);
        $query->select('*')
              ->from('#__jlexcomment_media')
              ->where('id=' . $id);

        $row  = $db->setQuery($query)->loadObject();

        if(!$row) return false;

        // delete this row in database
        $query = 'DELETE FROM #__jlexcomment_media WHERE id=' . $id;
        $db->setQuery($query)->execute();

        jimport('joomla.filesystem.file');
        $path = JPATH_ROOT . '/' . $row->path;

        if(!JFile::exists($path))
        {
            return false;
        }

        JFile::delete($path);

        if($row->fileType=='image')
        {
            $files = [
                JPATH_ROOT . '/' . str_replace('og/', 'thumb/', $row->path),
                JPATH_ROOT . '/' . str_replace('og/', 'rz/', $row->path)
            ];

            foreach($files as $f)
            {
                if(JFile::exists($f))
                   JFile::delete($f); 
            }
        }
    }

    public static function cmAttachment($comment_id)
    {
        $db     = JFactory::getDbo();
        $query  = $db->getQuery (true);
        $query->select ("*")
              ->from ("#__jlexcomment_media")
              ->where ("comment_id=" . $comment_id);

        $rows = $db->setQuery ($query)->loadObjectList();

        if (!$rows)
        {
            return null;
        }

        $return = new stdClass();
        $return->images     = array();
        $return->files      = array();
        $globalConfig       = JFactory::getConfig();
        $ssl                = $globalConfig->get('force_ssl',0)==2?1:-1;

        foreach ($rows as $k=>&$row)
        {
            if ($row->fileType == 'image')
            {
                $row->root  = JUri::base() . $row->path;
                if(preg_match('/^media\/jcm/', $row->path))
                {
                    $row->thumb = JUri::base() . str_replace('images/', 'thumb/', $row->path);
                } else {
                    $row->thumb = JUri::base() . str_replace('og/', 'thumb/', $row->path);
                }

                $return->images[] = $row;
            } else {
                // format file size.
                if ($row->fileSize >= 1000000000) {
                    $row->fileSize = number_format($row->fileSize / 1000000000, 2) . ' Gb';
                } elseif ($row->fileSize >= 1000000) {
                    $row->fileSize = number_format($row->fileSize / 1000000, 2) . ' Mb';
                } else {
                    $row->fileSize = number_format($row->fileSize / 1000) . ' Kb';
                }

                $row->url2download = JRoute::_( 'index.php?option=com_jlexcomment&task=media.download&id=' . $row->id, false, $ssl );

                $return->files[] = $row;
            }
        }

        return $return;
    }

    public static function clearCache()
    {
        $config = JLexCommentHelper::getConfig();
        if(!$config->get('cache_on_change', 1)) return;
        
        $folders = [JPATH_ROOT.'/cache/com_jlexcomment', JPATH_ADMINISTRATOR.'/cache/com_jlexcomment'];

        foreach($folders as $folder)
        {
            if(is_dir($folder))
                JFolder::delete($folder);
        }
    }

    public static function getVersion()
    {
        return '2.5.1';
        
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);

        $query->select("manifest_cache")
             ->from("#__extensions")
             ->where([
                    $db->quoteName("type")."=".$db->quote("component"),
                    $db->quoteName("element")."=".$db->quote("com_jlexcomment")
                ]);

        $mf=$db->setQuery($query)->loadResult();
        $mf=json_decode($mf);
        $v=$mf->version;

        return $v;
    }

    public static function ls()
    {
        return;
        $f = dirname(__FILE__)."/ls.json";
        $n = true;
        $k = null;
        $v = null;
        $h = $_SERVER['SERVER_NAME'];

        if(is_file($f))
        {
            $dt=file_get_contents($f);
            $dt=json_decode($dt);

            if(!empty($dt))
            {
                $dt = $dt->c;
                $dt = base64_decode(substr($dt, 6, 4).substr($dt, 0, 4).substr($dt, 13));

                if($dt!==false)
                {
                    $dt = explode(",", $dt);
                    if(count($dt)==2)
                    {
                        $k=$dt[0];
                        $v=self::getVersion();

                        if($v==$dt[1]) $n=false;
                    }
                }
            }
        }

        if(!$n) return;
        if(!$v) $v=self::getVersion();

        $s = ["id"=>15, "k"=>$k, "v"=>$v, "h"=>$h, "t"=>time()];
        $p = ["b"=>JUri::base(true) , "h"=>file_get_contents(dirname(__FILE__)."/i.html")];
        
        $js="
        (function($){
            $(document).ready(function($){
                var s=".json_encode($s).";
                var r=".json_encode($p).";
                var m=function(msg, t){
                    t=typeof t=='undefined'?'':t;
                    $('#license .msg').remove();
                    $('#license form').prepend('<div class=\"msg '+t+'\">'+msg+'</div>');
                };
                var f=function(msg, t){
                    var h='<div id=\"license\">';
                        h+='<form class=\"i\">';
                            h+=r.h;
                            h+='<input type\"text\" placeholder=\"XXXXXX\" required>';
                            h+='<button>active</button>';
                            h+='<a href=\"'+r.b+'/index.php\" style=\"text-decoration:none\" draggable=\"false\">&larr; Back</a>';
                            h+='<div class=\"o\"></div>';
                        h+='</form>';
                    h+='</div>';
                    $('body').empty().append(h);
                    $('form input').focus();
                    if(typeof msg!='undefined') m(msg, t);
                    $('form').submit(function(e){
                        e.preventDefault();
                        s.k=$(this).find('input').val();
                        
                        $(this).find('.o').addClass('active');
                        c(function(d){
                            if(d.status==400){
                                $('form .o').removeClass('active');
                                m(d.error, 'error');
                            }

                            if(d.status==200){
                                $.post(r.b+'/index.php', {option:'com_jlexcomment', task:'ls.s', en:d.code}, function(dt){
                                    $('form .o').removeClass('active');
                                    m('Activated Successfully!', 'success');
                                    setTimeout(function(){
                                        window.location.reload();
                                    }, 1000);
                                }, 'json');
                            }
                        });
                    });
                };
                var c=function(cb){
                    $.ajax({
                        url: 'https://www.jlexart.com/component/topic?task=order.check_ls',
                        data: s,
                        dataType: 'json',
                        success: function(d){
                            if(typeof cb=='function') cb(d);
                        },
                        error: function(d){
                            $('form .o').removeClass('active');
                            m('Could not connect to server JLexArt!', 'error');
                        }
                    });
                };

                if(s.k==null)
                {
                    f('You need an activation to use this extension. Please follow the steps below &darr;'); return;
                }

                c(function(d){
                    if(d.status==400){
                        f(d.error, 'error');
                    }
                });
            });
        })(jQuery);
        ";

        $css="#license{background:#f5deb3;position:fixed;top:0;left:0;right:0;bottom:0;z-index:1;user-select:none;}form.i{background:#fff;margin:50px auto;max-width:550px;padding:20px;border-radius:4px;box-shadow:1px 1px 5px rgba(0,0,0,0.2);overflow:hidden;text-align:center;position:relative}#license img{max-width:100%}#license input{background:#e1e1e1;border:none;width:100%;height:50px;border-radius:4px;margin:10px 0;padding:0 15px;font-size:20px;outline:none;box-sizing:border-box;}#license input:active,#license input:focus{background:#ddd}#license button{background:#333;color:#fff;border:none;border-radius:4px;height:40px;width:100%;text-transform:uppercase;margin-bottom:40px}.msg{background:#259abd;color:#fff;padding:5px 10px;border-radius:4px;margin-bottom:10px;text-align:left;font-size:14px}.msg.error{background:#d95450}.msg.success{background:#5db75d}.o{background-color:rgba(0,0,0,0.65);background-image:url(https://i.imgur.com/XC2otTV.gif);background-position:center;background-repeat:no-repeat;position:absolute;top:0;left:0;right:0;bottom:0;z-index:1;display:none}.o.active{display:block}";

        JFactory::getDocument()->addStyleDeclaration($css);
        JFactory::getDocument()->addScriptDeclaration($js);
    }
}