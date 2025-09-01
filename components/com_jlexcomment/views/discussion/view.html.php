<?php
/**
 * @package     JLex Comment
 * @version     1.0.0
 * @copyright   Copyright (C) 2013-2016 JLexArt Team (http://www.jlexart.com). All rights reserved.
 * @license     GNU/GPL or later
 * @author      JLexArt (support@jlexart.com)
 */

defined ( '_JEXEC' ) or die ();

class JLexCommentViewDiscussion extends JViewLegacy
{
    public $content = '';

    public function display( $tpl = null )
    {
        $app        = JFactory::getApplication();
        $document   = JFactory::getDocument();
        $menus      = $app->getMenu();
        $title      = null;

        $menu = $menus->getActive();

        if (!$menu || $menu->component!='com_jlexcomment' || @$menu->query['view']!='discussion') return;

        $title      = $menu->getParams()->get('page_title','')!='' ? $menu->getParams()->get('page_title') : $menu->title;
        $ogtitle    = $title;

        if (empty($title))
        {
            $title = $app->getCfg('sitename');
        }
        elseif ($app->getCfg('sitename_pagetitles', 0) == 1)
        {
            $title = JText::sprintf('JPAGETITLE', $app->getCfg('sitename'), $title);
        }
        elseif ($app->getCfg('sitename_pagetitles', 0) == 2)
        {
            $title = JText::sprintf('JPAGETITLE', $title, $app->getCfg('sitename'));
        }

        $document->setTitle($title);

        if ($menu->getParams()->get('show_page_heading')==1)
        {
            $heading = $menu->getParams()->get('page_heading','')!='' ? $menu->getParams()->get('page_heading') : $ogtitle;
        } else {
            $heading = null;
        }

        $this->set('heading', $heading);

        // body
        $object = $menu->getParams()->get('object');
        $object = preg_match('/^[A-z0-9\_]{2,}$/',$object) ? $object : 'menu';

        $object_id = intval($menu->getParams()->get('object_id'));
        $object_id = $object_id>0 ? $object_id : $menu->id;

        $loader = JPATH_ROOT . '/components/com_jlexcomment/load.php';
        if (is_file ( $loader )) {
            require_once $loader;
            $this->content = JLexCommentLoader::init ( $object, $object_id, $ogtitle );
        }

        parent::display($tpl);
    }
}
