<?php
/**
 * @package     JLex Comment
 * @version     1.0.0
 * @copyright   Copyright (C) 2013-2016 JLexArt Team (http://www.jlexart.com). All rights reserved.
 * @license     GNU/GPL or later
 * @author      JLexArt (support@jlexart.com)
 */

defined ( '_JEXEC' ) or die ();

class CommentViewReport extends JViewLegacy
{

	protected $reportings = null;

    protected $pagination = null;

    protected $state = null;

    public function display($tpl = null)
    {
        JToolbarHelper::title( JText::_("JCM_MN_REPORTING") );

        $this->reportings = $this->get('reports');
        $this->state = $this->get('state');
        $this->pagination = $this->get('pagaNav');

        JToolbarHelper::deleteList (JText::_("JCM_DELETE_ITEM_CONFIRM"), 'report.remove');

        parent::display($tpl);
    }
}
