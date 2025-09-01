<?php
/**
 * @package     JLex Comment
 * @version     1.0.0
 * @copyright   Copyright (C) 2013-2016 JLexArt Team (http://www.jlexart.com). All rights reserved.
 * @license     GNU/GPL or later
 * @author      JLexArt (support@jlexart.com)
 */

defined ( '_JEXEC' ) or die ();

class JLexCommentViewStyle extends JViewLegacy
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
        	JToolbarHelper::title(JText::_("JCM_STYLE"));
        	JToolbarHelper::save('style.save');
        	JToolbarHelper::cancel('style.cancel');

        	$this->form = $this->get('form');

            $script = '
                Joomla.submitbutton = function(task)
                {
                    if (task=="style.cancel" || document.formvalidator.isValid(document.getElementById("adminForm")))
                    {
                        Joomla.submitform(task, document.getElementById("adminForm"));
                    }
                };
            ';

            $this->document->addScriptDeclaration ($script);
        } else {
            JToolbarHelper::title(JText::_("JCM_MN_STYLE_GROUP"));
	        JToolbarHelper::addNew('style.add');

            JToolbarHelper::publishList('style.publish');
            JToolbarHelper::unpublishList('style.unpublish');
			
			JToolbarHelper::deleteList(JText::_("JCM_DELETE_ITEM_CONFIRM"),'style.delete');

	        $this->items = $this->get('styles');

	        $lists = array (
	        		'order' => $app->getUserStateFromRequest( "jcm.st.sortby", "filter_order", "s.id" ),
	        		'order_Dir' => $app->getUserStateFromRequest( "jcm.st.sortdir", "filter_order_Dir", "desc" ),
                    'query' => $app->getUserStateFromRequest( "jcm.st.query", "filter_search", "")
	        	);

            $lists['publish'] = JHtml::_('select.genericlist', 
                            array (
                                JHtml::_("select.option", "-1", "- Select Status -"),
                                JHtml::_("select.option", "1", "Published"),
                                JHtml::_("select.option", "0", "Unpublished")
                            ) , 
                            'filter_state', 
                            ' class="input-medium custom-select" onchange="Joomla.submitform();"', 
                            'value', 'text',
                            $app->getUserStateFromRequest( "jcm.st.publish", "filter_state", "-1"));

            
	        // pagination
	        $this->pagination = $this->get('pageNav');

	        $this->set('lists', $lists);
        }

        parent::display($tpl);
    }
}
