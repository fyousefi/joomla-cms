<?php
/**
 * @version     1.0.0
 * @package     Content - JLex Comment
 * @copyright   Copyright (C) 2013-2016 JLexArt Team. All rights reserved.
 * @license     GNU/GPL or later
 * @author      JLexArt.COM (support@jlexart.com)
 */

defined('_JEXEC') or die;

class plgContentJLexComment extends JPlugin
{
    protected $items    =   array();

    public function __construct(& $subject, $config)
    {
        // Make sure are JLex Comment installed.
        if (!file_exists(JPATH_ROOT . '/components/com_jlexcomment/jlexcomment.php'))
        {
            return false;
        }
        parent::__construct($subject, $config);
        $this->loadLanguage();
    }

    protected function _enabled( $context, &$row )
    {
        $parts  = explode(".", $context);
        $option = @$parts[0];

        if(!empty($option)) $option = str_replace("com_", "", $option);
        if(empty($option) || !in_array($option, array('content', 'k2', 'jdownloads', 'virtuemart', 'dpcalendar'))) return false;

        $id = 0;
        switch ($option)
        {
            case "content":
            case "k2":
                if(!isset($row->id)) return false;
                $id = $row->id;
                
                break;

            case "jdownloads":
                if(!isset($row->id)) return false;
                $id = $row->id;

                break;

            case "virtuemart":
                if(!isset($row->virtuemart_product_id)) return false;
                $id = $row->virtuemart_product_id;  
                break;

            case "dpcalendar":
                if(!isset($row->id)) return false;
                $id = $row->id;
                break;
        }


        $key = $option.'_'.$id;

        if(array_key_exists($key, $this->items)) return $this->items[$key];

        // not assign, find.
        $app   = JFactory::getApplication();
        $field = null;
        $item  = new stdClass();
        $item->id       = $id;
        $item->rid      = $app->input->getInt("id", 0);
        $item->enable   = false;
        $item->title    = '';
        $item->catid    = 0;
        $item->entry    = false;
        $item->option   = $option;

        // check if it's item layout
        $layout = $app->input->getCmd("option","") . "." . $app->input->getCmd("view", "");
        $typeOfallow = array(
            'com_content.article',
            'com_k2.item',
            'com_virtuemart.productdetails',
            'com_jdownloads.download',
            'com_dpcalendar.event'
        );

        if(in_array($layout, $typeOfallow)) $item->entry = true;


        switch($option)
        {
            case "content":
            case "k2":
                if ($option=='content' && in_array($context, array(
                        'com_content.categories'
                    ))) return false;

                $item->title  = $row->title;
                $item->catid  = @$row->catid;
                $item->featured = @$row->featured;
                
                $field  = "text";

                if($option=="content")
                {
                    if(!isset($row->text))
                    {
                        if(isset($row->fulltext)) $field='fulltext';
                        if(isset($row->introtext)) $field='introtext';
                    }
                }

                break;

            case "jdownloads":
                $item->title  = $row->file_title;
                $item->catid  = $row->cat_id;

                $field  = "description";

                break;

            case "virtuemart":
                if ($context!="com_virtuemart.productdetails") return false;

                $item->title  = $row->product_name;
                $item->catid  = $row->virtuemart_category_id;
                $item->rid    = $app->input->getInt("virtuemart_product_id", 0);  
                break;

            case "dpcalendar":
                $item->title  = $row->title;
                $item->catid  = $row->catid;
                break;
        }

        if (!empty($field))
        {
            $break = false;

            if(isset($row->$field))
            {
                if (stripos($row->$field, "{jlexcomment:off}") !== false)
                {
                    $item->enable = false;
                    $break = true;
                } elseif (stripos($row->$field, "{jlexcomment:on}") !== false) {
                    $item->enable = true;
                    $break = true;
                }

                $row->$field = str_replace(array(
                    "{jlexcomment:off}",
                    "{jlexcomment:on}"
                ), array(
                    "",
                    ""
                ), $row->$field);
            }

            if($break==true)
            {
                $this->items[$key] = $item;
                return $item;
            }
        }
        
        switch ($option)
        {
            case 'content':
                if($this->params->get('joomla_item',1)==0)
                {
                    $item->enable = false;
                } else {
                    $item->enable = $this->_contentCategoryPermission($item->catid);
                    if($item->enable && !$item->featured && $this->params->get('joomla_featured',0)==1)
                    {
                        $item->enable=false;
                    }
                }
                break;

            case 'k2':
                if ($this->params->get('k2_item',0)==0)
                {
                    $item->enable = false;
                } else {
                    $item->enable = $this->_k2CategoryPermission($item->catid);
                    if($item->enable && !$item->featured && $this->params->get('k2_featured',0)==1)
                    {
                        $item->enable=false;
                    }
                }
                break;

            case 'virtuemart':
                if($this->params->get('virtuemart_item',0)==0)
                {
                    $item->enable = false;
                } else {
                    $vmCats = $this->params->get('category_virtuemart', array(
                                '0'
                            ));

                    $item->enable = in_array('0', $vmCats) || in_array((string) $item->catid, $vmCats);
                }
                break;

            case 'jdownloads':
                if ($this->params->get('jdownloads_item',0)==0)
                {
                    $item->enable = false;
                } else {
                    $item->enable = $this->_jDownloadsCategoryPermission($item->catid);
                }
                break;

            case 'dpcalendar':
                $item->enable = $this->params->get('dpcalendar_item',0)==1?true:false;
                break;
        }

        // final
        $this->items[$key] = $item;
        return $item;
    }

    protected function _contentCategoryPermission($catid)
    {
        $Cats = $this->params->def('category', array(0));
        if (! is_array($Cats)) return false;

        foreach ($Cats as $k=>$id)
        {
            // convern to integer
            $Cats[$k] = $id*1;
        }

        // turn on for all categories
        if (in_array(0, $Cats) || in_array($catid, $Cats)) return true;

        // find if child
        if ($this->params->def('joomla_child',1)<1)
        {
            return false;
        } 

        $db = JFactory::getDbo();
        $parent = $catid;

        while ($parent > 1)
        {
            $query = "SELECT parent_id FROM #__categories WHERE extension='com_content' AND id=".$parent;
            $parent = $db->setQuery($query)->loadResult();
            if (! $parent) return false;

            $parent*=1;
            if (in_array($parent, $Cats)) return true;
        }

        return false;
    }

    protected function _k2CategoryPermission($catid)
    {
        $k2Cats = $this->params->def('category_k2', array(0));
        if (! is_array($k2Cats))
        {
            return false;
        }

        foreach ($k2Cats as $k=>$id) {
            // convern to integer
            $k2Cats[$k] = $id*1;
        }

        // turn on for all categories
        if (in_array(0, $k2Cats) || in_array($catid, $k2Cats)) return true;

        // find if child
        if ($this->params->def('k2_child',1)<1) return false;

        $db = JFactory::getDbo();
        $parent = $catid;

        while ($parent > 0) {
            $query = "SELECT parent FROM #__k2_categories WHERE id=".$parent;
            $parent = $db->setQuery($query)->loadResult();
            if (! $parent) return false;

            $parent*=1;
            if (in_array($parent, $k2Cats)) return true;
        }

        return false;
    }

    protected function _jDownloadsCategoryPermission( $catid )
    {
        $jdCats = $this->params->def('category_jdownloads', array(0));
        if (! is_array($jdCats))
        {
            return false;
        }

        foreach ($jdCats as $k=>$id) {
            // convern to integer
            $jdCats[$k] = $id*1;
        }

        // turn on for all categories
        if (in_array(0, $jdCats) || in_array($catid, $jdCats)) return true;

        // find if child
        if ($this->params->def('jdownloads_child',1)<1) return false;

        $db = JFactory::getDbo();
        $parent = $catid;

        while ($parent > 0) {
            $query = "SELECT parent_id FROM #__jdownloads_categories WHERE id=".$parent;
            $parent = $db->setQuery($query)->loadResult();
            if (! $parent) return false;

            $parent*=1;
            if (in_array($parent, $jdCats)) return true;
        }

        return false;
    }

    public function onContentPrepare($context, &$row, &$params, $page = 0)
    {
        $this->_enabled($context,$row);
    }
    

    public function onContentBeforeDisplay($context, &$row, &$params, $page = 0)
    {
        if(!$this->params->get("cm_count", 1)) return '';

        if(in_array($context, array(
                        'com_content.categories'
                    ))) return '';

        $info = $this->_enabled($context, $row);
        
        if(!$info || !$info->enable) return '';
        
        $loader = JPATH_ROOT . '/components/com_jlexcomment/load.php';
        require_once $loader;
        
        $url = "";
        switch ($info->option)
        {
            case "content":
                $url = JRoute::_(ContentHelperRoute::getArticleRoute(@$row->slug, @$row->catslug));
                break;

            case "k2":
                $url = $row->link;
                break;

            case "jdownloads":
                $url = JRoute::_('index.php?option=com_jdownloads&amp;view=download&amp;id='.$row->slug.'&amp;catid='.$row->cat_id);
                break;
        }
        
        $data = JLexCommentLoader::count_cm( $info->option, $info->id, null, false);

        $url  = !empty($data->url) ? $data->url : $url;

        $icon = '<img src="'.JUri::base(true).'/plugins/content/jlexcomment/assets/icon.png" />';
        $prefix = $data->cm_count > 0 ? ($data->cm_count>1?JText::sprintf("PLG_CONTENT_JCM_COMMENTS",$data->cm_count):JText::_("PLG_CONTENT_JCM_COMMENT")) : JText::_("PLG_CONTENT_JCM_WRITE_COMMENT");
        $html = '<a class="jcm-count-cm" href="'.$url.'#comment">'. $icon .' '. $prefix .'</a>';

        return $html;
    }

    public function onContentAfterDisplay($context, &$row, &$params, $page = 0)
    {
        $info = $this->_enabled( $context, $row );
        
        if (!$info || !$info->enable || !$info->entry) return '';

        $loader = JPATH_ROOT . '/components/com_jlexcomment/load.php';
        require_once $loader;

        return JLexCommentLoader::init($info->option, $info->id, $info->title);
    }

    /* iCagenda */
    public function onListAddEventInfo($context, &$item, &$params)
    {
        // Exclude admin and not authorized
        $app    = JFactory::getApplication ();
        $input  = JFactory::getApplication()->input;

        $iclist_show_count = $this->params->get('ministar', 1);

        if ($input->getCmd('option') === 'com_icagenda'
            && $iclist_show_count
            && $app->isSite())
        {
            $enable = $this->__iCagendaEnable($item);

            if($enable)
            {
                $loader = JPATH_ROOT . '/components/com_jlexcomment/load.php';
                require_once $loader;
                
                $output = JLexCommentLoader::count_cm( 'icagenda', $item->id, $item->url, true);
            
                return $output;
            }

            return false;
        }

        return false;
    }

    public function onEventAfterDisplay($context, &$item, &$params)
    {
        // Exclude admin and not authorized
        $app    = JFactory::getApplication ();
        $input  = JFactory::getApplication()->input;

        if ($input->getCmd('option') === 'com_icagenda'
            && $app->isSite())
        {
            $enable = $this->__iCagendaEnable($item);
            if($enable)
            {
                $loader = JPATH_ROOT . '/components/com_jlexcomment/load.php';
                require_once $loader;
            
                $output = JLexCommentLoader::init('icagenda', $item->id, $item->title);
                echo $output;
            }

            return true;
        }

        return false;
    }

    protected function __iCagendaEnable(&$item)
    {
        $enable=true;

        if($this->params->get('icagenda_item',0)==0)
        {
            return false;
        }

        // by category
        $allowCategories = $this->params->get('category_icagenda',array('0'));
        if(!in_array('0', $allowCategories))
        {
            $enable=in_array($item->catid, $allowCategories)?true:false;
        }


        // by syntax
        if(isset($item->desc))
        {
            if(preg_match('/{jlexreview\:on}/is', $item->desc))
            {
                $enable=true;
            } elseif(preg_match('/{jlexreview\:off}/is', $item->desc)) {
                $enable=false;
            }
        }

        // clear syntax
        $item->desc = preg_replace('/{jlexreview\:(off|on)}/is', '', $item->desc);

        return $enable;
    }
}