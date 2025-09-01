<?php
/**
 * @version		1.0.0
 * @package		Content - JLex Comment
 * @copyright	Copyright (C) 2013-2016 JLexArt Team. All rights reserved.
 * @license		GNU/GPL or later
 * @author		JLexArt.COM (support@jlexart.com)
 */

defined('_JEXEC') or die;

jimport ( 'joomla.form.formfield' );
class JFormFieldNotice extends JFormField
{
	protected $type = 'Notice';

	public function getInput()
	{
		$html = '<div style="background-color: #DFF0D8;border-color: #D6E9C6;color: #3C763D;border-radius: 4px;margin-bottom: 20px;padding: 15px;clear:both">';
		$html .= 'You can turn off JLex Comment by placing this code inside your content {jlexcomment:off} - Apply for <b>Only items selected</b>';
		$html .= '<hr/>';
		$html .= 'You can turn on JLex Comment by placing this code inside your content {jlexcomment:on} - Apply for <b>On all items except those selected</b>';
		$html .= '</div>';
		return $html;
	}
}