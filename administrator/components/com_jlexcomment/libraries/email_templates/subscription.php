<?php
/**
 * @package     JLex Comment
 * @version     1.0.0
 * @copyright   Copyright (C) 2013-2016 JLexArt Team (http://www.jlexart.com). All rights reserved.
 * @license     GNU/GPL or later
 * @author      JLexArt (support@jlexart.com)
 */

defined('_JEXEC') or die;
ob_start (); ?>

<h1 class="caption" style="font-weight:normal"><strong><?=$user->name?></strong>, <?=jtext::sprintf("JCM_NOF_EMAIL_HEADING", $configSys->get("sitename"))?></h1>

<?php foreach($comments as $e): ?>
	<div class="i-block">
		<a href="<?=$e->url.(preg_match("/\?/", $e->url)?'&':'?').'comment_id='.$e->id?>" target="_blank">
			<div class="h">
			<?php if($e->count_cm>1):?>
				<?=jtext::sprintf("JCM_NOF_PEOPLES_COMMENT_POSTING", '<strong>'.$e->author.'</strong>', '<strong>'.($e->count_cm-1).'</strong>', '<strong>'.$e->title.'</strong>')?>
			<?php else:?>
				<?=jtext::sprintf("JCM_NOF_PEOPLE_COMMENT_POSTING", '<strong>'.$e->author.'</strong>', '<strong>'.$e->title.'</strong>')?>
			<?php endif;?>
			</div>
			<div class="c"><?=$e->comment?></div>
		</a>
	</div>
<?php endforeach; ?>

<?php
if($total>7)
{
	echo '<hr>';
	echo '<div>'.jtext::sprintf("JCM_NOF_EMAIL_MORE_ENTRY", '<strong>'.($total-7).'</strong>').'</div>';
}
?>

<?php
$body = ob_get_contents();
ob_end_clean();

include dirname(__FILE__) . '/body.php';