<?php
/**
 * @package     JLex Comment
 * @version     1.0.0
 * @copyright   Copyright (C) 2013-2016 JLexArt Team (http://www.jlexart.com). All rights reserved.
 * @license     GNU/GPL or later
 * @author      JLexArt (support@jlexart.com)
 */

defined ( '_JEXEC' ) or die ();

class JLexCommentViewItems extends JViewLegacy
{
    public $items = null;

    public $pagination = null;

    protected $form = null;

    public function display($tpl = null)
    {
    	$app = JFactory::getApplication ();
        if(JE_JVERSION=="J3") JHtml::_('formbehavior.chosen', 'select' , '#component');

        $layout = $this->getLayout();

        if($layout=='form')
        {
        	JToolbarHelper::title(JText::_("JCM_ENTRY_FORM"));
        	JToolbarHelper::save('items.save');
        	JToolbarHelper::cancel('items.cancel');

        	$this->form = $this->get ('form');
        } else {
        	JToolbarHelper::title(JText::_("JCM_MN_ENTRIES"));
	        JToolbarHelper::addNew('items.add');
			JToolbarHelper::publishList ('items.publish');
			JToolbarHelper::unpublishList ('items.unpublish');
			JToolbarHelper::custom('items.truncate', 'trash', 'trash', JText::_("JCM_TRUNCATE"));
			JToolbarHelper::custom('items.recalculate', 'refresh', 'refresh', JText::_("JCM_RECALCULATE"));
			JToolbarHelper::divider();
			
			JToolbarHelper::deleteList(JText::_("JCM_DELETE_ITEM_CONFIRM"),'items.delete');

	        $this->items = $this->get('objects');

	        $lists = [
	        		"order" => $app->getUserStateFromRequest( "jcm.items.sortby", "filter_order", "obj.created_time" ),
	        		"order_Dir" => $app->getUserStateFromRequest("jcm.items.sortdir", "filter_order_Dir", "desc"),
	        		"q" => $app->getUserStateFromRequest("jcm.items.q", "q", "")
	        	];

	        // components
	        $lists["comps"] = $this->get("components");
	        if($lists['comps']!=null)
	        {
	        	$com_request = $app->getUserStateFromRequest( "jcm.items.component", "component", "" );
	        	$lists["comps"] = JHTML::_("select.genericlist", $lists["comps"] , "component", ' class="input-medium custom-select" onchange="Joomla.submitform();" ', "value", "text", $com_request);
	        }

	        // pagination
	        $this->pagination = $this->get("pageNav");

	        $this->set("lists", $lists);
        }

        parent::display($tpl);
    }
}
