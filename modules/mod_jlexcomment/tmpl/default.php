<?php
/**
 * @version		2.0.0
 * @package		JLex Comment
 * @subpackage	Module JLex Comment
 * @copyright	Copyright (C) 2013-2016 JLexArt. All rights reserved.
 * @license		GNU/GPL 2 or later
 * @author		JLexArt
 */
defined("_JEXEC") or die;

$type = $params->get("type", "latest_comment");
?>

<div class="jcm-mod">
<?php
switch ($type)
{
	case "latest_comment";
		$comments = $helper->latestComments();
		if ($comments)
		{
			include dirname(__FILE__) . "/default_latest_comment.php";
		}
		
		break;

	case "most_comment":
		$entries = $helper->getMostCommented();
		if ($entries)
		{
			include dirname(__FILE__) . "/default_most_commented.php";
		}
		
		break;

	case "top_poster":
		$posters = $helper->getTopPosters();
		if ($posters)
		{
			include dirname(__FILE__) . "/default_top_author.php";
		}
		
		break;

	case "comment_form":
		$cf = (int) $params->get("item_source", 1);
		switch($cf)
		{
			case 1:
			case 2:
				$obj_title = trim($params->get("cf_entry_title",""));
				$title = $obj_title==""?$module->title:$obj_title;
				$id = $cf==1?$module->id:$params->get("cf_entry_id");
				$component = $cf==1?"module":$params->get("cf_entry_type");

				if(preg_match("/^[A-z0-9]+$/", $component) && preg_match("/^[1-9][0-9]*$/", $id) && !$app->get('jcm-form', false))
				{
					$app->set('jcm-form', true);

					require_once JPATH_ROOT . "/components/com_jlexcomment/load.php";
					echo JLexCommentLoader::init($component, $id, $title);
				}
				break;

			case 3:
				$fname = $params->get("cf_entry_type", "");
				$fid = $params->get("cf_var_id", "");

				if(preg_match("/^\s*$/", $fname))
				{
					$fname = $app->input->getCmd("option", "")."_".$app->input->getCmd("view", "noview");
				}

				if(preg_match("/^[A-z0-9\_]+$/", $fname) && preg_match("/^[A-z0-9\_]+$/", $fid))
				{
					$fid = $app->input->getInt($fid, 0);
					if(!$app->get('jcm-form', false))
					{
						$app->set('jcm-form', true);

						if(is_array($fid))
						{
							$cid = [];
							foreach($fid as $int_id)
							{
								if(preg_match('/^[1-9][0-9]*$/', $int_id)) $cid[]=$int_id;
							}

							if(empty($cid)) return;

							asort($cid);
							$fid = implode('.', $cid);
						} elseif($fid<1) {
							return;
						}

						require_once JPATH_ROOT . "/components/com_jlexcomment/load.php";
						$title = JFactory::getDocument()->getTitle();
						echo JLexCommentLoader::init($fname, $fid, $title);
					}
				}
				break;
		}
		break;
}
?>
</div>
