<?php
/**
 * @package     JLex Comment
 * @version     1.0.0
 * @copyright   Copyright (C) 2013-2016 JLexArt Team (http://www.jlexart.com). All rights reserved.
 * @license     GNU/GPL or later
 * @author      JLexArt (support@jlexart.com)
 */

defined ( '_JEXEC' ) or die ();

class JLexCommentViewMailQ extends JViewLegacy
{
    public $items       = null;

    public $pagination  = null;

    protected $config   = null;

    public function display($tpl = null)
    {
    	$app = JFactory::getApplication();

        $this->config = JLexCommentHelper::getConfig();

        JToolbarHelper::title(JText::_("JCM_MN_MAILQ"));
        
        JToolbarHelper::custom('mailq.markpending', 'unpublish', 'unpublish', JText::_("JCM_MARK_AS_PENDING"));
        JToolbarHelper::custom('mailq.marksent', 'publish', 'publish', JText::_("JCM_MARK_AS_SENT"));

        $this->items = $this->get('MailQuery');

        $lists = array (
                'order' => $app->getUserStateFromRequest ( "jcm.sg.sortby", "filter_order", "nof.created_time" ),
                'order_Dir' => $app->getUserStateFromRequest ( "jcm.sg.sortdir", "filter_order_Dir", "desc" )
            );

        // pagination
        $this->pagination = $this->get('pageNav');

        $this->set('lists', $lists);

        parent::display($tpl);
    }
}
