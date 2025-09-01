<?php
/**
 * @package     JLex Comment
 * @version     1.0.0
 * @copyright   Copyright (C) 2013-2016 JLexArt Team (http://www.jlexart.com). All rights reserved.
 * @license     GNU/GPL or later
 * @author      JLexArt (support@jlexart.com)
 */

defined ( '_JEXEC' ) or die ();

class JLexCommentViewBlacklist extends JViewLegacy
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
        	JToolbarHelper::title(JText::_("JCM_BLACKLIST_FORM"));
        	JToolbarHelper::save('blacklist.save');
        	JToolbarHelper::cancel('blacklist.cancel');

        	$this->form = $this->get('form');

            $script = '
                Joomla.submitbutton = function(task)
                {
                    if (task=="blacklist.cancel" || document.formvalidator.isValid(document.getElementById("adminForm")))
                    {
                        Joomla.submitform(task, document.getElementById("adminForm"));
                    }
                };
            ';

            $this->document->addScriptDeclaration ($script);

        } else {
            JToolbarHelper::title(JText::_("JCM_BLACKLIST_MANAGER"));
	        JToolbarHelper::addNew('blacklist.add');

			JToolbarHelper::deleteList(JText::_("JCM_DELETE_ITEM_CONFIRM"),'blacklist.delete');

	        $this->items = $this->get('blacklist');

	        $lists = array (
	        		'order' => $app->getUserStateFromRequest("jcm.bls.sortby", "filter_order", "bls.created_time" ),
	        		'order_Dir' => $app->getUserStateFromRequest("jcm.bls.sortdir", "filter_order_Dir", "desc" ),
                    'query' => $app->getUserStateFromRequest("jcm.bls.query", "filter_search", "")
	        	);

            // pagination
	        $this->pagination = $this->get('pageNav');

	        $this->set('lists', $lists);
        }

        parent::display($tpl);
    }
}
