<?php
/**
 * @package     JLex Comment
 * @version     1.0.0
 * @copyright   Copyright (C) 2013-2016 JLexArt Team (http://www.jlexart.com). All rights reserved.
 * @license     GNU/GPL or later
 * @author      JLexArt (support@jlexart.com)
 */

defined ( '_JEXEC' ) or die ();

class JLexCommentController extends JControllerLegacy
{
    public function display($cachable = false, $urlparams = false)
    {
        $app = JFactory::getApplication();
        $config = JLexCommentHelper::getConfig();
        $request = $app->input;
        
        // get the document object.
        $document = JFactory::getDocument();

        // call tooltip plugin
        if(JE_JVERSION=="J3") JHtml::_('behavior.tooltip');
        JHtml::_('jquery.framework');
        CommentHelperAdmin::ls();

        $assets = JUri::base(true)."/components/com_jlexcomment/assets";

        $document->addStyleSheet($assets."/style.css?v=3");
        $document->addScript($assets."/chart.min.js");
        $document->addScript($assets."/script.js?v=3");

        if(JE_JVERSION=="J4")
        {
            $document->addStyleSheet($assets."/j4.css");
            $document->addScript($assets."/j4.js");
        } else {
            $document->addStyleSheet($assets."/j3.css");
        }
        
        // set the default view name and format from the Request.
        $vName   = $this->input->getCmd('view', 'dashboard');
        $vFormat = $document->getType();
        $lName   = $this->input->getCmd('layout', 'default');

        if(JE_JVERSION=="J4" && $vName=="settings")
        {
            $jsCustom = "(function($){
    $(document).ready(function(){
        $('#jlexcomment .nav-tabs a').click(function(){
            let p=$(window).scrollTop();
            let f=setInterval(function(){
                $(window).scrollTop(p);
            },1);

            setTimeout(function(){
                clearInterval(f);
            }, 1000);
        });
    });
})(jQuery);";

            $document->addScriptDeclaration($jsCustom);
        }

        $view   = $this->getView($vName, $vFormat);
        $model  = $this->getModel($vName);

        $view->setModel($model, true);

        $jsContent = "";

        switch ($vName)
        {
            case 'dashboard':
                $view->statistics = $model->statistics();
                $view->others = $model->others();

                $jsContent = "
                    jcm.dashboard(".json_encode($view->statistics).",".json_encode($view->others).");
                ";

                $this->input->set('view', 'dashboard');
                break;


            case 'comments':
                $jsContent = "
                    jcm.comments();
                ";
                break;

            case 'item':
                $id = $request->getInt ('id',0);
                $model->set ('id', $id);
                $jsContent = "";

                if($id>0)
                {
                    // try get media
                    $media = $model->getMedia();
                    if($media)
                    {
                        $media = json_encode($media);
                        $jsContent = "
                            window.jcm_media = {$media};
                        ";
                    }
                }

                $jsContent.= "
                    jcm.comment();
                ";
                break;

            case "sticker":
            case "roles":
            case "replacer":
            case "stickergroup":
            case "subscription":
            case 'items':
                if($lName=='form')
                {
                    $id = $request->getInt ('id',0);
                    $model->set('id', $id);
                }
                break;

            case "blacklist":
                if ($lName=='form')
                {
                    $id = $request->getInt('id',0);
                    $model->set('id', $id);
                }

                $jsContent.= "
                    jcm.blacklist ();
                ";
                break;

            case "users":
                $jsContent.= "
                    jcm.users();
                ";
                break;

            case "import":
                $jsContent.= "
                    jcm.import();
                ";
                break;

            case 'integration':
                break;

            case 'sync':
                if($lName=='form')
                {
                    $view->setLayout('form');
                    $form = $model->getForm();
                    $keep = $app->getUserState('jcm.sync');

                    if(empty($keep))
                    {
                        $id = $app->input->getInt('id', 0);
                        if($id>0)
                        {
                            $item = $model->getCallback($id);
                            if(!empty($item)) $form->bind($item);
                        }
                    } else {
                        $form->bind($keep);
                        $app->setUserState('jcm.sync', null);
                    }

                    $view->set('form', $form);
                    $document->addScript( JUri::base(true) . '/components/com_jlexcomment/assets/ace/jquery.ace.js' );

                    $jsContent = "
                        window.path2ace='".JUri::base(true)."/components/com_jlexcomment/assets/ace/libs';
                    ";
                } else {
                    $view->set('items', $model->getCallbacks());
                    $view->set('pagination', $model->getPagination());
                
                    $jsContent = "
                        window.path2admin='".JUri::base(true)."';
                    ";
                }
                break;

            case 'style':
                if ($lName=='form')
                {
                    $id = $request->getInt ('id',0);
                    $model->set ('id', $id);
                }
                $jsContent = "
                    jcm.style();
                ";
                break;
        }

        $view->setLayout($lName);
            
        // Push document object into the view.
        $view->document = $document;

        if(JE_JVERSION=="J3")
        {
            $listViews = array (
                'dashboard' => JText::_("JCM_MN_DASHBOARD"),
                'items'     => JText::_("JCM_MN_ENTRIES"),
                'comments'  => JText::_("JCM_MN_COMMENTS"),
                'settings'  => JText::_("JCM_MN_SETTINGS"),
                'stickergroup' => JText::_("JCM_MN_STICKER_GROUP"),
                'sticker'   => JText::_("JCM_MN_STICKER"),
                'subscription' => JText::_("JCM_MN_SUBSCRIPTION"),
                'blacklist' => JText::_("JCM_MN_BLACKLIST"),
                'import'    => JText::_("JCM_MN_IMPORT"),
                'reporting' => JText::_("JCM_MN_REPORTING"),
                'roles'     => JText::_("JCM_MN_ROLES"),
                'replacer'  => JText::_("JCM_MN_REPLACER"),
                'style'  => JText::_("JCM_STYLE"),
                'users'     => JText::_("JCM_MN_USERS"),
                'mailq'     => JText::_("JCM_MN_MAILQ"),
                'integration' => JText::_("JCM_MN_INTEGRATION"),
                'sync'  => JText::_("JCM_MN_SYNC")
            );

            $jsContent.="
                jcm.j3(".json_encode($listViews).", '".$vName."');
            ";
        }

        // extra javascript
        $baseUrl = JUri::base (true);
        $js = "
            (function($){
                $(document).ready(function(){
                    var jcm = new JLexCommentAdmin($, '{$baseUrl}');
                    {$jsContent};
                });
            })(jQuery);
        ";
        $document->addScriptDeclaration($js);
        
        $view->display();
    }
}
