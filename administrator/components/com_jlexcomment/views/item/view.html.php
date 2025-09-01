<?php
/**
 * @package     JLex Comment
 * @version     1.0.0
 * @copyright   Copyright (C) 2013-2016 JLexArt Team (http://www.jlexart.com). All rights reserved.
 * @license     GNU/GPL or later
 * @author      JLexArt (support@jlexart.com)
 */

defined ( '_JEXEC' ) or die ();

class JLexCommentViewItem extends JViewLegacy
{
    protected $form = null;

    public function display($tpl = null)
    {
    	$app = JFactory::getApplication ();
        if(JE_JVERSION=="J3") JHtml::_('formbehavior.chosen', 'select' , '.selectAdvanced');

        JToolbarHelper::title(JText::_("JCM_COMMENT_FORM"));
        JToolbarHelper::apply('item.apply');
        JToolbarHelper::save('item.save');
        JToolbarHelper::cancel('item.cancel');

        $this->form = $this->get ('form');

        if($this->form===false) return;

        $script = '
            Joomla.submitbutton = function(task)
            {
                if (task=="item.cancel" || document.formvalidator.isValid(document.getElementById("adminForm")))
                {
                    Joomla.submitform(task, document.getElementById("adminForm"));
                }
            };
        ';

        $this->document->addScriptDeclaration($script);

        parent::display($tpl);
    }
}
