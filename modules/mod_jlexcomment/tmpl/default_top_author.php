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
?>

<div class="jcm-mod-users-box <?php echo $params->get("moduleclass_sfx",""); ?>">
	<ul class="jcm-mod-users">
	<?php foreach ($posters as $k=>$user): ?>
		<li>
			<?php if (!empty($user->thumbnail)): ?>
				<?php if(!empty($user->url)): ?>
				<a class="_thumbnail_box" href="<?php echo $user->url; ?>">
				<?php endif; ?>
					<img class="_thumbnail" src="<?php echo $user->thumbnail; ?>" />
				<?php if(!empty($user->url)): ?>
				</a>
				<?php endif; ?>
			<?php endif; ?>

			<div class="_content">
				<?php if(!empty($user->url)): ?>
				<a href="<?php echo $user->url; ?>">
				<?php endif; ?>
					<span class="_author_name"><?php echo $user->author_name; ?></span>
				<?php if(!empty($user->url)): ?>
				</a>
				<?php endif; ?>

				<?php if ($params->def("tp_count_cm",1)==1): ?>
				<span class="_count"><?php echo $user->cm_count>1?JText::sprintf("JCM_MOD_COMMENTS_COUNT",$user->cm_count):JText::sprintf("JCM_MOD_COMMENT_COUNT",$user->cm_count); ?></span>
				<?php endif; ?>
			</div>

			<div class="clearfix"></div>
		</li>
	<?php endforeach; ?>
	</ul>

<?php
if ($params->def("pagination",0)==1)
{
	echo $helper->getPagination()->getPagesLinks();
}
?>
</div>