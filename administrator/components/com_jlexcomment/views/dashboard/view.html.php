<?php
/**
 * @package     JLex Comment
 * @version     1.0.0
 * @copyright   Copyright (C) 2013-2016 JLexArt Team (http://www.jlexart.com). All rights reserved.
 * @license     GNU/GPL or later
 * @author      JLexArt (support@jlexart.com)
 */

defined ( '_JEXEC' ) or die ();

class JLexCommentViewDashboard extends JViewLegacy
{

    public $statistics  = null;

    public $others      = null;

    protected $sidebar  = null;

    public function display($tpl = null)
    {
        JToolbarHelper::title(JText::_("JCM_MN_DASHBOARD"));

        $this->sidebar = CommentHelperAdmin::sidebar ( 'dashboard' );

        parent::display($tpl);
    }
}
