<?php
/**
 * @package     JLex Comment
 * @version     1.0.0
 * @copyright   Copyright (C) 2013-2016 JLexArt Team (http://www.jlexart.com). All rights reserved.
 * @license     GNU/GPL or later
 * @author      JLexArt (support@jlexart.com)
 */

defined ( '_JEXEC' ) or die ();

class JLexCommentViewSync extends JViewLegacy
{
    protected $form = null;
    
    protected $items = null;

    protected $pagination = null;

    public function display($tpl = null, $toolbar = true)
    {
        $app = JFactory::getApplication();
        $layout = $this->getLayout();
        
        $doc = JFactory::getDocument();

        if($layout=='form')
        {
            $doc->setTitle( JText::_("JCM_SYNC_FORM") );
            JToolbarHelper::title( JText::_("JCM_SYNC_FORM") );

            JToolbarHelper::save('sync.save');
            JToolbarHelper::cancel('sync.cancel');
        } else {
            $asset_path = JUri::base(true).'/components/com_jlexcomment/assets';
            $doc->addScript($asset_path.'/sync.js');

            $doc->setTitle(JText::_("JCM_SYNC") );
            $prefix = "com_jcm.sync.";
        
            JToolbarHelper::title( JText::_("JCM_SYNC") );
            JToolbarHelper::addNew('sync.add');
            JToolbarHelper::publishList('sync.publish');
            JToolbarHelper::unpublishList('sync.unpublish');

            if(is_array($this->items) && count($this->items))
            {
                JToolBarHelper::custom('sync', 'refresh', 'refresh', jtext::_('JCM_SYNC_BTN'), false);
            }

            $lists = array(
                'order' => $app->getUserStateFromRequest( $prefix . 'filter_order', 'filter_order', 's.created_time' ),
                'order_dir' => $app->getUserStateFromRequest( $prefix . 'filter_order_dir', 'filter_order_dir', 'DESC' )
            );

            $this->set('lists', $lists);
        }

        parent::display();
    }
}
