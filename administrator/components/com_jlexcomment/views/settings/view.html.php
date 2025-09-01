<?php
/**
 * @package     JLex Comment
 * @version     1.0.0
 * @copyright   Copyright (C) 2013-2016 JLexArt Team (http://www.jlexart.com). All rights reserved.
 * @license     GNU/GPL or later
 * @author      JLexArt (support@jlexart.com)
 */

defined ( '_JEXEC' ) or die ();

class JLexCommentViewSettings extends JViewLegacy
{
    protected $form     = null;

    protected $groups   = null;

    protected $permissionForms = null;

    public function display($tpl = null)
    {
    	$app = JFactory::getApplication ();
        if(JE_JVERSION=="J3") JHtml::_('formbehavior.chosen', 'select');

        JToolbarHelper::title(JText::_("JCM_MN_SETTINGS") );
        JToolbarHelper::apply('settings.save');

        $this->sidebar = CommentHelperAdmin::sidebar ( 'settings' );
        $this->form = $this->get('form');

        $this->groups = $this->get('UserGroups');
        $this->permissionForms = $this->get('PermissionForms');

        parent::display($tpl);
    }
}
