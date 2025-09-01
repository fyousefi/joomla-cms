<?php
/**
 * @package     JLex Comment
 * @version     1.0.0
 * @copyright   Copyright (C) 2013-2016 JLexArt Team (http://www.jlexart.com). All rights reserved.
 * @license     GNU/GPL or later
 * @author      JLexArt (support@jlexart.com)
 */

defined ( '_JEXEC' ) or die ();

class JLexCommentViewComments extends JViewLegacy
{

    public $comments    = null;

    public $pagination  = null;

    protected $form     = null;

    protected $config   = null;

    public function display($tpl = null)
    {
    	$app = JFactory::getApplication();
        $this->config = JLexCommentHelper::getConfig();
        if(JE_JVERSION=="J3") JHtml::_('formbehavior.chosen', 'select' , '.selectAdvanced');

        $layout = $this->getLayout ();

    	JToolbarHelper::title(JText::_("JCM_COMMENT_MANAGER"));
        JToolbarHelper::addNew('comments.add');
		JToolbarHelper::publishList ('comments.publish');
		JToolbarHelper::unpublishList ('comments.unpublish');

		JToolbarHelper::custom ('comments.feature', 'featured', 'featured', JText::_("JCM_FEATURE"));
		JToolbarHelper::custom ('comments.unfeature', 'unfeatured', 'unfeatured', JText::_("JCM_UNFEATURE"));
		
		JToolbarHelper::deleteList (JText::_("JCM_DELETE_ITEM_CONFIRM"),'comments.delete');

        JToolbarHelper::custom ('comments.recalculate', 'refresh', 'cleartmp', JText::_("JCM_CLEAR_TMP_FILE"), false);

        $this->comments = $this->get('comments');

        $lists = [
        		"order" => $app->getUserStateFromRequest( "jcm.comments.sortby", "filter_order", "cm.created_time"),
        		"order_Dir" => $app->getUserStateFromRequest("jcm.comments.sortdir", "filter_order_Dir", "desc" ),
                "query" => $app->getUserStateFromRequest( "jcm.comments.query", "q", "")
        	];

        // sort by special rows
        $sortList = [
            'cm.created_time desc' 	=> 'Date descending',
    		'cm.created_time asc' 	=> 'Date ascending',
            'cm.report_count desc'   => jtext::_('JCM_REPORT_COUNT'),
    		'cm.published desc' 	=> 'Published descending',
    		'cm.published asc' 		=> 'Published ascending',
    		'cm.featured desc' 		=> 'Featured descending',
    		'cm.featured asc' 		=> 'Featured ascending',
    		'cm.id desc' 			=> 'ID descending',
    		'cm.id asc' 			=> 'ID ascending',
    		'obj.title desc' 		=> 'Entry name descending',
    		'obj.title asc' 		=> 'Entry name ascending',
    		'(cm.up_point-cm.down_point) desc'	=> 'Point descending',
    		'(cm.up_point-cm.down_point) asc' => 'Point ascending'
            
        ];

        $lists["sort"] = JHTML::_('select.genericlist', 
        					$sortList , 
        					'sortbyfull', 
        					' class="input-medium selectAdvanced custom-select"', 
        					'value', 'text', $lists["order"] . ' ' . $lists["order_Dir"]);

        // pagination
        $this->pagination = $this->get('pageNav');

        $this->set('lists', $lists);

        parent::display($tpl);
    }
}
