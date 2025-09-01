<?php
/**
 * @package     JLex Comment
 * @version     1.0.0
 * @copyright   Copyright (C) 2013-2016 JLexArt Team (http://www.jlexart.com). All rights reserved.
 * @license     GNU/GPL or later
 * @author      JLexArt (support@jlexart.com)
 */

defined ( '_JEXEC' ) or die ();

class JLexCommentViewLicense extends JViewLegacy
{

    public $items       = null;

    public $pagination  = null;

    protected $form     = null;

    public function display($tpl = null)
    {
    	$app = JFactory::getApplication ();
        JHtml::_('formbehavior.chosen', 'select');

        $this->sidebar = CommentHelperAdmin::sidebar ( 'license' );
        JToolbarHelper::title(JText::_("JCM_LICENSE_TITLE"));

        parent::display($tpl);
    }
}
