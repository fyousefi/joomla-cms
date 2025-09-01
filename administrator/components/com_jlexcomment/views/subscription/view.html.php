<?php
/**
 * @package     JLex Comment
 * @version     1.0.0
 * @copyright   Copyright (C) 2013-2016 JLexArt Team (http://www.jlexart.com). All rights reserved.
 * @license     GNU/GPL or later
 * @author      JLexArt (support@jlexart.com)
 */

defined ( '_JEXEC' ) or die ();

class JLexCommentViewSubscription extends JViewLegacy
{
    public $items       = null;

    public $pagination  = null;

    protected $form     = null;

    public function display($tpl = null)
    {
    	$app = JFactory::getApplication ();
        if(JE_JVERSION=="J3") JHtml::_('formbehavior.chosen', 'select');

        $layout = $this->getLayout();

        if($layout=='form')
        {
        	JToolbarHelper::title(JText::_("JCM_SUBSCRIPTION_FORM"));
        	JToolbarHelper::save('subscription.save');
        	JToolbarHelper::cancel('subscription.cancel');

        	$this->form = $this->get ('form');

            $script = '
                Joomla.submitbutton = function(task)
                {
                    if (task=="subscription.cancel" || document.formvalidator.isValid(document.getElementById("adminForm")))
                    {
                        Joomla.submitform(task, document.getElementById("adminForm"));
                    }
                };
            ';

            $this->document->addScriptDeclaration ($script);

        } else {
            JToolbarHelper::title(JText::_("JCM_MN_SUBSCRIPTION") );
	        JToolbarHelper::addNew('subscription.add');

            JToolbarHelper::custom('subscription.enable', 'publish', 'publish', JText::_("JCM_ACTIVATE"));
            JToolbarHelper::custom('subscription.disable', 'unpublish', 'unpublish', JText::_("JCM_BLOCK"));
			
			JToolbarHelper::deleteList( JText::_("JCM_DELETE_ITEM_CONFIRM"),'subscription.delete');

	        $this->items = $this->get('Users');

	        $lists = array(
	        		'order' => $app->getUserStateFromRequest("jcm.sub.sortby", "filter_order", "sub.created_time"),
	        		'order_Dir' => $app->getUserStateFromRequest("jcm.sub.sortdir", "filter_order_Dir", "desc" ),
                    'query' => $app->getUserStateFromRequest("jcm.sub.query", "filter_search", "")
	        	);

            // pagination
	        $this->pagination = $this->get('pageNav');

	        $this->set('lists', $lists);
        }

        parent::display($tpl);
    }
}
