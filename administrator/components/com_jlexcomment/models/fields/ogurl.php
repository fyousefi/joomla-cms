<?php
/**
 * @package     JLex Comment
 * @version     1.0.0
 * @copyright   Copyright (C) 2013-2016 JLexArt Team (http://www.jlexart.com). All rights reserved.
 * @license     GNU/GPL or later
 * @author      JLexArt (support@jlexart.com)
 */

defined ( '_JEXEC' ) or die ();

class JFormFieldJCM_OgUrl extends JFormField
{
	/**
	 * The form field type.
	 *
	 * @var    string
	 * @since  1.6
	 */
	public $type = 'JCM_OgUrl';

	/**
	 * Method to get the user field input markup.
	 *
	 * @return  string  The field input markup.
	 *
	 * @since   1.6
	 */
	protected function getInput()
	{
		$type = $this->getAttribute("method", "fb");
		$url  = JUri::root()."index.php?option=com_jlexcomment&task=user.oauth&type=".$type;

		if($type=="twitter")
			$url = JUri::root()."index.php";

		$html='';

		switch ($type) {
			case "cronjob":
				$url = "wget -O - -q -t 1 '".JUri::root()."index.php?option=com_jlexcomment&task=subscribe.mail' >/dev/null 2>&1";

				$html = '<input type="text" class="form-control" value="'.$url.'" readonly onclick="this.select();">';
				break;

			case "cronjob_sync":
				$url = "wget -O - -q -t 1 '".JUri::root()."index.php?option=com_jlexcomment&task=subscribe.sync' >/dev/null 2>&1";

				$html = '<input type="text" class="form-control" value="'.$url.'" readonly onclick="this.select();">';
				break;

			case "cronjob_task":
				$url = "wget -O - -q -t 1 '".JUri::root()."index.php?option=com_jlexcomment&task=subscribe.task' >/dev/null 2>&1";

				$html = '<input type="text" class="form-control" value="'.$url.'" readonly onclick="this.select();">';
				break;

			case "cronjob_guide":
				$html = '<a href="http://www.jlexart.com/documentation/jlex-comment#section-6-5" target="_blank">' . JText::_('JCM_SEE_DOC') . '</a>';
				break;

			case "facebook":
				$html = '<div class="alert alert-info">';
					$html.= '<span>'. JText::_('JCM_FB_OAUTH_GUIDE_SETUP') .':</span><br>';
						$html.= '<input type="text" class="jcm-cf-url" value="' . JUri::root() . 'component/jlexcomment/?task=user.oauth&type=fb&access=1" readonly onclick="this.select();">';
						$html.= '<input type="text" class="jcm-cf-url" value="' . JUri::root() . 'index.php?option=com_jlexcomment&task=user.oauth&type=fb&access=1" readonly onclick="this.select();">';
				$html.= '</div>';
				break;
			
			default:
				$html = '<input type="text" class="form-control" value="'.$url.'" readonly onclick="this.select();">';
				break;
		}

		return $html;
	}
}
