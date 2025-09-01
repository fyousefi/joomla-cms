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

<div class="jcm-mod-latest-comment">
<?php foreach ($comments as $k=>$cm):?>
	<div class="jcm-mod-comment">
		<div class="jcm-mod-info">
			<?php if (!empty($cm->author_thumb)): ?>
				<?php if (!empty($cm->author_link)): ?>
				<a class="jcm-mod-thumb-url" href="<?php echo $cm->author_link; ?>">
				<?php endif; ?>
					<img class="jcm-mod-thumb" src="<?php echo $cm->author_thumb ?>" />
				<?php if (!empty($cm->author_link)): ?>
				</a>
				<?php endif; ?>
			<?php endif; ?>
			<div class="jcm-mod-extra">
				<?php if (!empty($cm->author_link)): ?>
				<a href="<?php echo $cm->author_link; ?>">
				<?php endif; ?>
					<span class="jcm-mod-author"><?php echo $cm->author_name ?></span>
				<?php if (!empty($cm->author_link)): ?>
				</a>
				<?php endif; ?>

				<?php if(!empty($cm->created_time)): ?>
				<span class="jcm-mod-date"><?php echo $cm->created_time; ?></span>
				<?php endif; ?>

				<?php if(!empty($cm->entry_title) && !empty($cm->created_time)): ?><span class="jcm-dot"></span><?php endif; ?>

				<?php if(!empty($cm->entry_title)): ?>
				<a class="jcm-mod-entry" href="<?php echo $cm->url ?>"><?php echo $cm->entry_title ?></a>
				<?php endif; ?>
			</div>
			<div class="clearfix"></div>
		</div>

		<div class="jcm-mod-url" data-href="<?php echo $cm->url ?>">
			<div class="jcm-mod-content">
				<?php echo $cm->comment ?>
			</div>

			<?php if(!empty($cm->media)): ?>
			<div class="jcm-mod-media">
				<?php if(count($cm->media->images)): ?>
				<span><?php echo JText::sprintf("JCM_MOD_IMAGE_ATTACHED",count($cm->media->images))?></span>
				<?php endif; ?>

				<?php if(count($cm->media->files)): ?>
				<span><?php echo JText::sprintf("JCM_MOD_FILE_ATTACHED",count($cm->media->files)); ?></span>
				<?php endif; ?>
			</div>
			<?php endif; ?>
		</div>
	</div>
<?php endforeach; ?>

<?php
if ($params->def("pagination",0)==1)
{
	echo $helper->getPagination()->getPagesLinks();
}
?>
</div>