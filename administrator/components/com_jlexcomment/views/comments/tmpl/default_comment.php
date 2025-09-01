<?php
/**
 * @package     JLex Comment
 * @version     1.0.0
 * @copyright   Copyright (C) 2013-2016 JLexArt Team (http://www.jlexart.com). All rights reserved.
 * @license     GNU/GPL or later
 * @author      JLexArt (support@jlexart.com)
 */

defined ( '_JEXEC' ) or die;
JFactory::getDocument()->addStyleDeclaration('.jcm-colls{overflow: hidden; text-overflow: ellipsis; display: -webkit-box; -webkit-line-clamp: 5; -webkit-box-orient: vertical;}');
?>
<div class="jcm-cm-row">
	<div class="jcm-colls">
		<?php echo $this->comment->locked==1?'<span class="icon-lock fa fa-lock" title="'.Jtext::_('JCM_PRIVATE_COMMENT').'"></span> ':'' ?>
		<?=$this->comment->comment?></div>
	<div class="jcm-cm-meta">
		<a class="jcm-extra l" href="<?=$this->comment->url2edit?>"><?=jtext::_("JCM_EDIT")?></a>
		<a class="jcm-cm-quick jcm-extra l" href="#"><?=jtext::_("JCM_PREVIEW")?></a>

		<?php if($this->comment->published==1):?>
		<a class="jcm-extra l" href="<?=$this->comment->url2frontend?>" target="_blank"><?=jtext::_("JCM_IN_FRONT_END")?></a>
		<?php endif; ?>

		<?php
			if($this->comment->media!=null)
			{
				if(count($this->comment->media->images))
					echo '<span class="jcm-extra i">'.jtext::_("JCM_IMAGE").'</span>';

				if(count($this->comment->media->files))
					echo '<span class="jcm-extra f">'.jtext::_("JCM_FILE").'</span>';
			}
		?>
	</div>
</div>

<div class="jcm-cm-full">
	<div class="_inner">
		<a class="btn btn-primary btn-block jcm-full-exist" href="#"><?=jtext::_("JCM_CANCEL")?></a>

		<hr/>

		<div class="jcm-post-body">
			<?=$this->comment->comment?>
		</div>

		<?php
			// media
			if($this->comment->media!=null):
				// images
				if(count($this->comment->media->images)):
					?>
					<hr>
					<div class="_caption"><?=jtext::_("JCM_IMAGE_ATTACHED")?></div>
					<ul class="jcm-img-list jcm-inline">
					<?php
					foreach($this->comment->media->images as $img):
						?>
						<li>
							<img src="<?=$img->thumb?>" />
							<a class="hasTooltip" href="<?php echo $img->root ?>" title="<?=$img->html?>" target="_blank"><?=$img->name?></a>
						</li>
						<?php
					endforeach;
					?>
					</ul>
					<?php
				endif;

				// files
				if(count($this->comment->media->files)):
					?>
					<hr>
					<div class="jcm-files-attached">
						<div class="_caption"><?php echo JText::_("JCM_FILE_ATTACHED"); ?></div>
						<ol>
						<?php
						foreach($this->comment->media->files as $file):
							?>
							<li>
								<a href="<?=$file->url2download?>" class="_title">
									<span><?=$file->name?></span>
								</a>
								<?php if(!preg_match("/^\s*$/", $file->description)): ?>
									<p class="_desc" style="font-size:0.9em;"><?=$file->description?></p>
								<?php endif; ?>
							</li>
							<?php
						endforeach;
						?>
						</ol>
					</div>
					<?php
				endif;
			endif;
		?>
	</div>
</div>