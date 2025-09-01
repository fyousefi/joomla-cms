<?php
/**
 * @version		1.0.0
 * @package		JLex Comment
 * @subpackage	Module JLex Comment
 * @copyright	Copyright (C) 2013-2016 JLexArt. All rights reserved.
 * @license		GNU/GPL 2 or later
 * @author		JLexArt
 */
defined ( "_JEXEC" ) or die;

class JFormFieldLabel extends JFormField
{
	var $type = 'label';
	
	public function getInput()
	{
		return '<div style="background: #d5e7fa; border-bottom: 2px solid #96b0cb; clear: both; color: #369;float: left; font-size: 12px;font-weight: bold;margin: 12px 0 4px;padding: 5px 7px;width: 100%"><div class="paramHeaderContent">' . JText::_ ( $this->value ) . '</div><div class="clearfix"></div></div>';
	}
}