<?php
/**
 * @package     JLex Comment
 * @version     1.0.0
 * @copyright   Copyright (C) 2013-2016 JLexArt Team (http://www.jlexart.com). All rights reserved.
 * @license     GNU/GPL or later
 * @author      JLexArt (support@jlexart.com)
 */

defined ( '_JEXEC' ) or die ();

class JLexCommentViewReporting extends JViewLegacy
{

    public $items       = null;

    public $pagination  = null;

    protected $form     = null;

    public function display($tpl = null)
    {
    	$app = JFactory::getApplication ();
        if(JE_JVERSION=="J3") JHtml::_('formbehavior.chosen', 'select');

        JToolbarHelper::title(JText::_("JCM_MN_REPORTING"));

        JToolbarHelper::deleteList(JText::_("JCM_DELETE_ITEM_CONFIRM"),'reporting.delete');

        $this->items = $this->get('Reports');

        $lists = array (
                'order' => $app->getUserStateFromRequest ( "jcm.rep.sortby", "filter_order", "r.created_time" ),
                'order_Dir' => $app->getUserStateFromRequest ( "jcm.rep.sortdir", "filter_order_Dir", "desc" ),
                'query' => $app->getUserStateFromRequest ( "jcm.rep.query", "filter_search", "")
            );

        // pagination
        $this->pagination = $this->get('pageNav');
        $this->set('lists', $lists);

        parent::display($tpl);
    }
}
