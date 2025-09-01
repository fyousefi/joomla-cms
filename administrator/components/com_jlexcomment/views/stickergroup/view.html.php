<?php
/**
 * @package     JLex Comment
 * @version     1.0.0
 * @copyright   Copyright (C) 2013-2016 JLexArt Team (http://www.jlexart.com). All rights reserved.
 * @license     GNU/GPL or later
 * @author      JLexArt (support@jlexart.com)
 */

defined ( '_JEXEC' ) or die ();

class JLexCommentViewStickerGroup extends JViewLegacy
{
    public $items       = null;

    public $pagination  = null;

    protected $form     = null;

    public function display($tpl = null)
    {
    	$app = JFactory::getApplication();
        if(JE_JVERSION=="J3") JHtml::_('formbehavior.chosen', 'select');

        $layout = $this->getLayout();

        if($layout=='form')
        {
        	JToolbarHelper::title( JText::_("JCM_STICKER_GROUP_FORM") );
        	JToolbarHelper::save('stickergroup.save');
        	JToolbarHelper::cancel('stickergroup.cancel');

        	$this->form = $this->get('form');

            $script = '
                Joomla.submitbutton = function(task)
                {
                    if (task=="stickergroup.cancel" || document.formvalidator.isValid(document.getElementById("adminForm")))
                    {
                        Joomla.submitform(task, document.getElementById("adminForm"));
                    }
                };
            ';

            $this->document->addScriptDeclaration ($script);

        } else {
        	JToolbarHelper::title(JText::_("JCM_MN_STICKER_GROUP"));
	        JToolbarHelper::addNew('stickergroup.add');

            JToolbarHelper::publishList('stickergroup.publish');
            JToolbarHelper::unpublishList('stickergroup.unpublish');
			
			JToolbarHelper::deleteList(JText::_("JCM_DELETE_ITEM_CONFIRM"),'stickergroup.delete');

	        $this->items = $this->get('StickerGroups');

	        $lists = [
	        		'order' => $app->getUserStateFromRequest("jcm.sg.sortby", "filter_order", "s.created_time" ),
	        		'order_Dir' => $app->getUserStateFromRequest("jcm.sg.sortdir", "filter_order_Dir", "desc" ),
                    'query' => $app->getUserStateFromRequest("jcm.sg.query", "filter_search", "")
	        	];

	        // pagination
	        $this->pagination = $this->get('pageNav');

	        $this->set('lists', $lists);
        }

        parent::display($tpl);
    }
}
