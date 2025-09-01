<?php
/**
 * @package     JLex Comment
 * @version     1.0.0
 * @copyright   Copyright (C) 2013-2016 JLexArt Team (http://www.jlexart.com). All rights reserved.
 * @license     GNU/GPL or later
 * @author      JLexArt (support@jlexart.com)
 */

defined ( '_JEXEC' ) or die ();

class JLexCommentViewUsers extends JViewLegacy
{
    public $items       = null;

    public $pagination  = null;

    public function display($tpl = null)
    {
    	$app = JFactory::getApplication();

        JToolbarHelper::title( JText::_("JCM_MN_USERS"));

        JToolbarHelper::custom('users.outbl', 'publish', 'publish', JText::_("JCM_ADD_USER_WHITELIST"));
        JToolbarHelper::custom('users.add2bl', 'unpublish', 'unpublish', JText::_("JCM_ADD_USER_BLACKLIST"));

        JToolbarHelper::custom('users.clearthumb', 'unpublish', 'unpublish', JText::_("JCM_CLEAR_THUMB"));
        JToolbarHelper::custom('users.clearcm', 'unpublish', 'unpublish', JText::_("JCM_CLEAR_COMMENT"));

        $this->items = $this->get('Users');

        $lists = array (
                'order' => $app->getUserStateFromRequest ( "jcm.u.sortby", "filter_order", "u.registerDate" ),
                'order_Dir' => $app->getUserStateFromRequest ( "jcm.u.sortdir", "filter_order_Dir", "desc" ),
                'query' => $app->getUserStateFromRequest ( "jcm.u.query", "filter_search", "")
            );

        // pagination
        $this->pagination = $this->get('pageNav');

        $this->set('lists', $lists);

        parent::display($tpl);
    }
}
