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
    protected $jcm_vars = [];

    public function setParam($param, $value)
    {
        $this->jcm_vars[$param] = $value;
    }

    public function getParam($param, $default=null)
    {
        return array_key_exists($param, $this->jcm_vars)?$this->jcm_vars[$param]:$default;
    }

    public function display($cachable=false, $urlparams=array())
    {
        $app       = JFactory::getApplication();
        $doc       = JFactory::getDocument();
        $request   = $app->input;
        $user      = JFactory::getUser();
        $config    = JLexCommentHelper::getConfig();
        $format    = $app->input->getCmd("format", "html");
        
        if(!$this->getParam('locked',false))
        {
            $viewip = $app->input->getCmd ("view", "items");

            // special views
            if($viewip=="user" || $viewip=="discussion")
            {
                $view = $this->getView($viewip, "html");

                if($viewip=="user")
                {
                    $model = $this->getModel("user");
                    $view->setModel($model);
                }

                $view->display();
                return;
            }
        }

        $view = $this->getView("items", $format);
    	
        if($format=="html")
        {
            $asset = JUri::root(true) . "/components/com_jlexcomment/assets";
            if($config->get("css_default",1)==1)
                $doc->addStyleSheet($asset."/jcm/style.css?v=3.4.2");

            if($config->get("rtl",0)==1)
                $doc->addStyleSheet($asset."/jcm/rtl.css?v=2.4.6");

            $doc->addStyleSheet($asset."/css/tribute.css");

            // custom css & js
            if(!preg_match('/^\s*$/', $config->get('custom_css','')))
            {
                $doc->addStyleDeclaration($config->get('custom_css'));
            }

            if(!preg_match('/^\s*$/', $config->get('custom_js','')))
            {
                $doc->addScriptDeclaration($config->get('custom_js'));
            }

            // indent level
            $child_style = $config->get('child_style', '1');
            if($child_style=='1')
            {
                $css = '#jlexcomment:not(.jcm-xsmall) jcm-level-1 .jcm-list-reply {margin-left: -45px;}';
                $doc->addStyleDeclaration($css);
            }

            // jquery
            JHtml::_('behavior.keepalive');
            JHtml::_('jquery.framework');

            if($config->get("location",0))
            {
                $doc->addScript('https://maps.googleapis.com/maps/api/js?key='.$config->get("map_api_key","").'&v=3.exp&libraries=places');
            }

            $doc->addScript($asset."/tribute.min.js");

            // fontawesome
            if($config->get("fontawesome",1))
            {
                if($config->get("fontawesome_v", "v5")=="v5"){
                    $doc->addStyleSheet("https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.1/css/all.min.css");
                } else {
                    $doc->addStyleSheet("https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css");
                }
            }
            
            /* lightgallery */
            if($config->get("lightboxjs",1)==1)
            {
                $doc->addStyleSheet($asset."/lightgallery/css/f.css");
                $doc->addScript($asset."/lightgallery/js/f.js");
            }

            /* croper */
            if(!$user->guest && $config->get("profile_3rd","jlexcomment")=="jlexcomment")
            {
                $doc->addStyleSheet($asset."/cropper.min.css");
                $doc->addScript($asset."/cropper.min.js");
            }

            // EMOJI
            if($config->get("emoji", 1))
            {
                $doc->addStyleSheet($asset."/emojipanel.css");
                $doc->addScript($asset."/emojipanel.js");
                if($config->get('twemoji',1))
                    $doc->addScript("https://cdn.jsdelivr.net/npm/@twemoji/api@latest/dist/twemoji.min.js", [], ["crossorigin"=>"anonymous"]);
            }

            $doc->addScript($asset."/script.js?v=3.4.2");
        }

    	$model = $this->getModel("items", "JLexCommentModel");
    	$model->set("com_name", $this->getParam("com_name"));
    	$model->set("com_key", $this->getParam("com_key"));
        $model->set("com_title", $this->getParam("com_title"));
        $model->set("com_url", $this->getParam("com_url"));
        $model->set("sort", $config->get("sort", "best"));

        // use for amp
        $key = $app->input->getString('k', '');
        $amp = JLexCommentHelper::decodeSub($key);
        if($amp)
        {
            $amp_title = $app->input->getString("title", "");
            $amp_url = urldecode($app->input->getString("url", ""));

            $model->set("amp", true);
            $model->set("com_name", $amp->com_name);
            $model->set("com_key", $amp->com_key);
            if(!preg_match('/^\s*$/', $amp_title)) 
                $model->set("com_title", $amp_title);
            
            if(!preg_match('/^\s*$/', $amp_url))
                $model->set("com_url", $amp_url);

            $app->input->set("tmpl", "component");
            $view->set("amp", true);
        }

        $page = $request->getInt('page_comment', 1);
        $comment_id = $request->getInt('comment_id', 0);

        if($comment_id>0)
            $model->set("comment_id", $comment_id);
        
        if($page>1)
            $model->set("page", $page);

        if($format=="html")
        {
            // key to post comment
            if (preg_match("/^[A-z0-9\_\.\-]+$/", $this->getParam("com_name")) && preg_match("/^[A-z0-9\_\.\-]+$/", $this->getParam("com_key")))
            {
                $key = JLexCommentHelper::encodeSub($this->getParam("com_name") , $this->getParam("com_key"));
            } else {
                $key = null;
            }

            $cmConfig = new stdClass();
            $cmConfig->key = $key;
            $cmConfig->timestamp = JFactory::getDate()->toUnix();
            //$cmConfig->request = JRoute::_("index.php?option=com_jlexcomment", false);

            $cmConfig->request = JUri::root(true).'/index.php?option=com_jlexcomment';

            $view->set('cmConfig', $cmConfig);

            // link to post comment
            $url2save = JUri::root(true).'/index.php?option=com_jlexcomment&task=item.save';
            //JRoute::_('index.php?option=com_jlexcomment&task=item.save', false);
            $view->set('url2save', $url2save);

        	$view->setModel($model, true);

            // form plugin trigger
            $form_plugin = JLexCommentHelper::dispatcher("onFormDisplay");
            $view->set("form_plugin", $form_plugin);

            // add template path
            JLexCommentHelper::loadThemes($view);

            $cache          = $model->getCacheTurnOn();
            $cacheUnique    = $model->getCacheUnique();

            if($cache==true&&!empty($cacheUnique))
            {
                // get cache
                $cache = JFactory::getCache('com_jlexcomment', 'callback');
                $cache->setCaching(true);
                $cache->setLifeTime($config->get('cache_time')*1);

                $renderOutput = $cache->get(array($view, 'render'), array(), $cacheUnique, false);
            } else {
                $renderOutput = $view->render();
            }

            if($amp)
            {
                echo $renderOutput;
                return;
            }

            // jlex helpful
            if($config->get('helpful',0) && preg_match('/^[1-9][0-9]*$/', $config->get('helpful_id',1)))
            {
                $renderOutput='{{jlexhelpful name="'.htmlspecialchars($this->getParam('com_title')).'" key="'.$this->getParam('com_name').'_'.$this->getParam('com_key').'" section_id="'.$config->get('helpful_id',1).'" }}' . $renderOutput;
            }

            return $renderOutput;

        } else {
            if($config->get("rss",0)==0)
            {
                $app->redirect(JUri::root());
                return false;
            }

            $obj_name = $app->input->getCmd("com_name","");
            $obj_id = $app->input->getCmd("com_key","");

            // make sure this item is exist
            $db = JFactory::getDbo();
            $query = $db->getQuery(true);

            $query->select("COUNT(*)")
                  ->from("#__jlexcomment_obj")
                  ->where([
                        "com_name=".$db->quote($obj_name),
                        "com_key=".$db->quote($obj_id)
                    ]);
            $return = $db->setQuery($query)->loadResult();

            if(!$return)
            {
                throw new Exception(jtext::_("JCM_PAGE_NOT_FOUND"), 404);
                return;
            }
            
            $model->set("com_name", $obj_name);
            $model->set("com_key", $obj_id);
            $view->data = $model->getComments();

            // feed display
            $view->display();
        }
    }
}