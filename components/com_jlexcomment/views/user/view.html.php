<?php
/**
 * @package     JLex Comment
 * @version     1.0.0
 * @copyright   Copyright (C) 2013-2016 JLexArt Team (http://www.jlexart.com). All rights reserved.
 * @license     GNU/GPL or later
 * @author      JLexArt (support@jlexart.com)
 */

defined ( '_JEXEC' ) or die ();

class JLexCommentViewUser extends JViewLegacy
{
    public $activity;

    public function display( $tpl=null )
    {
    	$app        = JFactory::getApplication();
        $doc   		= JFactory::getDocument();
        $menus      = $app->getMenu();
        $title      = null;

        $menu = $menus->getActive();

        if (!$menu || $menu->component!='com_jlexcomment' || @$menu->query['view']!='user') return;

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

        $doc->setTitle($title);

        if ($menu->getParams()->get('show_page_heading')==1)
        {
            $heading = $menu->getParams()->get('page_heading','')!='' ? $menu->getParams()->get('page_heading') : $ogtitle;
        } else {
            $heading = null;
        }

        $this->set('heading', $heading);

        // body
        $user 	= JFactory::getUser();
		$limit 	= 10;
		$offset	= $app->input->getInt("limitstart",0);

		if ($user->guest)
		{
			throw new Exception(JText::_("JCM_PAGE_NOT_FOUND"), 404);
			return false;
		}

		$doc->addStylesheet( JUri::base(true) . "/components/com_jlexcomment/assets/3rd.css");

		$model = $this->getModel("user");
		$model->set("uid", $user->id);
		$model->set("offset", $offset);

		$this->activity = $model->list_comment();
		$this->activity->user = JFactory::getUser($user->id);

		// pagination for comment
		jimport ( 'joomla.html.pagination' );
        $this->activity->pagination = new JPagination ( $this->activity->total, $offset, $limit );

    	parent::display($tpl);
    }
    
    public function render( $tpl = null )
    {
    	$this->addTemplatePath(dirname(__FILE__) . "/tmpl");
        return parent::loadTemplate($tpl);
    }
}
