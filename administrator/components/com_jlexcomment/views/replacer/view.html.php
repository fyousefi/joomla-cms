<?php
/**
 * @package     JLex Comment
 * @version     1.0.0
 * @copyright   Copyright (C) 2013-2016 JLexArt Team (http://www.jlexart.com). All rights reserved.
 * @license     GNU/GPL or later
 * @author      JLexArt (support@jlexart.com)
 */

defined ( '_JEXEC' ) or die ();

class JLexCommentViewReplacer extends JViewLegacy
{

    public $items       = null;

    public $pagination  = null;

    protected $form     = null;

    public function display($tpl = null)
    {
    	$app = JFactory::getApplication ();
        JHtml::_('formbehavior.chosen', 'select');

        $layout = $this->getLayout();

        if($layout=='form')
        {
        	JToolbarHelper::title(JText::_("JCM_REPLACER_FORM"));
        	JToolbarHelper::save('replacer.save');
        	JToolbarHelper::cancel('replacer.cancel');

        	$this->form = $this->get('form');

            $script = '
                Joomla.submitbutton = function(task)
                {
                    if (task=="replacer.cancel" || document.formvalidator.isValid(document.getElementById("adminForm")))
                    {
                        Joomla.submitform(task, document.getElementById("adminForm"));
                    }
                };
            ';

            $this->document->addScriptDeclaration ($script);
        } else {
        	JToolbarHelper::title( JText::_("JCM_MN_REPLACER") );
	        JToolbarHelper::addNew('replacer.add');

            JToolbarHelper::publishList ('replacer.publish');
            JToolbarHelper::unpublishList ('replacer.unpublish');
			
			JToolbarHelper::deleteList (JText::_("JCM_DELETE_ITEM_CONFIRM"),'replacer.delete');

	        $this->items = $this->get('ReplaceClauses');

	        $lists = array (
	        		'order' => $app->getUserStateFromRequest("jcm.rp.sortby", "filter_order", "r.created_time" ),
	        		'order_Dir' => $app->getUserStateFromRequest("jcm.rp.sortdir", "filter_order_Dir", "desc" ),
                    'query' => $app->getUserStateFromRequest("jcm.rp.query", "filter_search", "")
	        	);

	        // pagination
	        $this->pagination = $this->get('pageNav');

	        $this->set('lists', $lists);
        }

        parent::display($tpl);
    }
}
