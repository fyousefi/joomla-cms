<?php
/**
 * @package     JLex Comment
 * @version     1.0.0
 * @copyright   Copyright (C) 2013-2016 JLexArt Team (http://www.jlexart.com). All rights reserved.
 * @license     GNU/GPL or later
 * @author      JLexArt (support@jlexart.com)
 */

defined ( '_JEXEC' ) or die ();

class JLexCommentViewSticker extends JViewLegacy
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
        	JToolbarHelper::title(JText::_("JCM_STICKER_FORM"));
        	JToolbarHelper::save('sticker.save');
        	JToolbarHelper::cancel('sticker.cancel');

        	$this->form = $this->get('form');

            $script = '
                Joomla.submitbutton = function(task)
                {
                    if (task=="sticker.cancel" || document.formvalidator.isValid(document.getElementById("adminForm")))
                    {
                        Joomla.submitform(task, document.getElementById("adminForm"));
                    }
                };
            ';

            $this->document->addScriptDeclaration ($script);
        } else {
            if($layout=="modal")
                $app->input->set("tmpl", "component");

        	JToolbarHelper::title(JText::_("JCM_MN_STICKER_GROUP"));
	        JToolbarHelper::addNew('sticker.add');

            JToolbarHelper::publishList('sticker.publish');
            JToolbarHelper::unpublishList('sticker.unpublish');
			
			JToolbarHelper::deleteList(JText::_("JCM_DELETE_ITEM_CONFIRM"),'sticker.delete');

	        $this->items = $this->get('Stickers');

	        $lists = array (
	        		'order' => $app->getUserStateFromRequest("jcm.s.sortby", "filter_order", "s.created_time" ),
	        		'order_Dir' => $app->getUserStateFromRequest("jcm.s.sortdir", "filter_order_Dir", "desc" ),
                    'query' => $app->getUserStateFromRequest("jcm.s.query", "filter_search", "")
	        	);

            $lists['publish'] = JHTML::_ ( 'select.genericlist', 
                            array (
                                JHTML::_("select.option", "-1", "- Select Status -"),
                                JHTML::_("select.option", "1", "Published"),
                                JHTML::_("select.option", "0", "Unpublished")
                            ) , 
                            'filter_state', 
                            ' class="input-medium custom-select" onchange="Joomla.submitform();"', 
                            'value', 'text',
                            $app->getUserStateFromRequest ( "jcm.sk.publish", "filter_state", "-1") );

            // filter by group
            $model = $this->getModel("sticker");
            $groups = $model->list_group();

            $options = array (
                    JHtml::_("select.option", "0", "- User -"),
                    JHtml::_("select.option", "-1", "- Select Group -")
                );

            if(count($groups))
            {
                foreach($groups as $group)
                {
                    $options[] = JHtml::_("select.option", $group->id, $group->name);
                }
            }

            $lists ['group'] = JHtml::_( 'select.genericlist', 
                            $options , 
                            'filter_group', 
                            ' class="input-medium custom-select" onchange="Joomla.submitform();" ', 
                            'value', 'text',
                            $app->getUserStateFromRequest ( "jcm.sk.group", "filter_group", "-1") );

	        // pagination
	        $this->pagination = $this->get('pageNav');

	        $this->set('lists', $lists);
        }

        parent::display($tpl);
    }
}
