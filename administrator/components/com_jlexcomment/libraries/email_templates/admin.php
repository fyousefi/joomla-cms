<?php
/**
 * @package     JLex Comment
 * @version     1.0.0
 * @copyright   Copyright (C) 2013-2016 JLexArt Team (http://www.jlexart.com). All rights reserved.
 * @license     GNU/GPL or later
 * @author      JLexArt (support@jlexart.com)
 */

defined ( '_JEXEC' ) or die;
ob_start (); ?>

<div style="font-size:12px;margin-bottom:15px"><i><?php echo $email; ?></i></div>
<h1 class="caption"><?php echo $title ?></h1>
<?php foreach ($comments as $k=>$item): ?>
	<div class="cm-block">
		<?php echo jtext::sprintf("JCM_EMAIL_ADMIN_INTRO_TEXT", '<a style="color:#5F5F5F;" href="'.$item->url.'" target="_blank">'.$item->author.'</a>', '<strong>'.$item->entry_name.'</strong>') ?>

		<div class="quote">
			<?php echo $item->comment; ?>

			<?php if($item->attachments): ?>
			<div class="cm-attachment">
				<hr>
				<div><strong><?php echo JText::_('JCM_ATTACHMENT') ?></strong></div>
				<?php
					if(count($item->attachments->images))
					{
						foreach($item->attachments->images as $img)
						{
							?>
							<span class="cm-file-block">
								<a href="<?php echo $img->root ?>" target="_blank">
									<img src="<?php echo $img->thumb ?>">
								</a>
								<span class="file-caption"><?php echo $img->name ?></span>
								<?php if(!preg_match('/^\s*$/', $img->description)):?>
								<div class="file-desc"><?php echo $img->description ?></div>
								<?php endif; ?>
							</span>
							<?php
						}
					}
				?>

				<?php
					if(count($item->attachments->files))
					{
						foreach($item->attachments->files as $file)
						{
							?>
							<span class="cm-file-block">
								<span class="file-caption"><?php echo $file->name ?></span>
								<?php if(!preg_match('/^\s*$/', $file->description)):?>
								<div class="file-desc"><?php echo $file->description ?></div>
								<?php endif; ?>
								<a href="<?php echo $file->url2download ?>" target="_blank"><?php echo JText::_('JCM_PM_DOWNLOAD_FILE') ?> (<?php echo $file->fileSize ?>)</a>
							</span>
							<?php
						}
					}
				?>
			</div>
			<?php endif;?>

			<div style="margin-top:5px;text-align:center">
				<a class="btn" href="<?php echo $item->url ?>"><?php echo jtext::_("JCM_NOF_VIEW_IT") ?></a>
			</div>
		</div>
		
		<?php if ($quicklinks==true): ?>
		<div class="_quicklinks">
			<?php if (!$item->published):?>
			<a href="<?php echo $item->url2publish ?>" target="_blank"><?php echo jtext::_("JCM_PUBLISH") ?></a>
			<?php else: ?>
			<a href="<?php echo $item->url2unpublish ?>" target="_blank"><?php echo jtext::_("JCM_UNPUBLISH") ?></a>
			<?php endif; ?>
			<a href="<?php echo $item->url2featured ?>" target="_blank"><?php echo jtext::_("JCM_FEATURE") ?></a>
			<a href="<?php echo $item->url2delete ?>" target="_blank" style="color:#ff4747"><?php echo jtext::_("JCM_DELETE") ?></a>
		</div>
		<?php endif; ?>
	</div>
<?php endforeach; ?>

<style>
span.cm-file-block{max-width:120px;display:inline-block;vertical-align:top;margin:0 10px 10px 0}
.file-caption{font-size:10px;font-weight:700}
.file-desc{font-size:10px}
hr{border:none;height:1px;background:#D1D1D1}
</style>

<?php
$body = ob_get_contents();
ob_end_clean();

$unsubscribe = '';
include dirname(__FILE__) . '/body.php';