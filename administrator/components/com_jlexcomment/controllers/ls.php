<?php
/**
 * @package     JLex Comment
 * @version     2.0.0
 * @copyright   [C] JLexArt (jlexart.com)
 * @license     GNU/GPL2 or later
 * @author      JLexArt.COM
 */

defined('_JEXEC') or die;

require_once JPATH_COMPONENT . '/controller.php';

class JLexCommentControllerLs extends JControllerLegacy
{
	public function s()
	{
		$app = JFactory::getApplication();
		$en  = $app->input->post->getString("en", "");

		file_put_contents(dirname(__FILE__)."/../libraries/ls.json", json_encode(["status"=>200, "c"=>$en]));

		JLexCommentHelper::mix2json(["status"=>200]);
	}
}
