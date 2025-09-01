<?php
/**
 * @package     JLex Comment
 * @version     1.0.0
 * @copyright   Copyright (C) 2013-2016 JLexArt Team (http://www.jlexart.com). All rights reserved.
 * @license     GNU/GPL or later
 * @author      JLexArt (support@jlexart.com)
 */

defined ( '_JEXEC' ) or die ();

class JLexCommentViewImport extends JViewLegacy
{
    public $items       = null;

    public $pagination  = null;

    protected $form     = null;

    public function display($tpl = null)
    {
    	$app = JFactory::getApplication ();
        if(JE_JVERSION=="J3") JHtml::_('formbehavior.chosen', 'select');

        $layout = $this->getLayout();

        JToolbarHelper::title(JText::_("JCM_MN_IMPORT"));

        if ($layout=="form")
        {
            JToolbarHelper::custom ('import.progress', 'publish', 'publish', JText::_("JCM_IMPORT"), false);
            $this->form = $this->get('form');

            $script = '
                Joomla.submitbutton = function(task)
                {
                    if (document.formvalidator.isValid(document.getElementById("adminForm")))
                    {
                        Joomla.submitform(task, document.getElementById("adminForm"));
                    }
                };
            ';

            $this->document->addScriptDeclaration($script);

        } else {
            $this->items = $this->get("apps");

            $migrators = null;
            if($this->items)
            {
                $options = array();
                foreach($this->items as $item)
                {
                    $text = $item["name"] . " (". $item["count"] .")";
                    $options[] = JHtml::_("select.option", $item["key"], $text);
                }

                $migrators = JHtml::_ ( 'select.genericlist', 
                            $options , 
                            'migrator', 
                            ' class="input-medium custom-select" ', 
                            'value', 'text' );
            }

            $this->set("migrators", $migrators);
        }

        parent::display($tpl);
    }
}
