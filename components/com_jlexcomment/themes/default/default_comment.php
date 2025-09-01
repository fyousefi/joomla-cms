<?php
/**
 * @package     JLex Comment
 * @version     1.0.0
 * @copyright   Copyright (C) 2013-2016 JLexArt Team (http://www.jlexart.com). All rights reserved.
 * @license     GNU/GPL or later
 * @author      JLexArt (support@jlexart.com)
 */

defined ( '_JEXEC' ) or die ();
$schema = $this->config->get('schema',1)==1 ? '' : 'data-';

$locked = $this->cm->locked==1 && !$this->cm->me && !in_array(3, $this->user->getAuthorisedViewLevels());

?>
<div class="jcm-post-header">
	<!-- Name/date/report-flag/expand-collapse -->
	<span class="jcm-author">
		<?php if (!empty($this->cm->author_link)) :?>
			<a href="<?php echo $this->cm->author_link ?>" <?php echo $schema; ?>itemprop="author" <?php echo $schema; ?>itemscope <?php echo $schema; ?>itemtype="http://schema.org/Person">
				<span <?php echo $schema; ?>itemprop="name"><?php echo $this->cm->author_name; ?></span>
			</a>
		<?php else: ?>
			<span class="jcm-user-cm" data-id="<?php echo $this->cm->created_by ?>" <?php echo $schema; ?>itemprop="author" <?php echo $schema; ?>itemscope <?php echo $schema; ?>itemtype="http://schema.org/Person">
				<span <?php echo $schema; ?>itemprop="name"><?php echo $this->cm->author_name; ?></span>
			</span>
		<?php endif; ?>

		<?php // roles ?>
		<?php if ( $this->config->def('jcm_layout_role',1)==1 && is_array($this->cm->roles) && count($this->cm->roles) > 0): ?>
			<ul class="jcm-inline jcm-roles">
				<?php foreach ($this->cm->roles as $role): ?>
					<li style="background:<?php echo $role->colour ?>"><?php echo $role->title ?></li>
				<?php endforeach; ?>
			</ul>
		<?php endif; ?>
	</span>

	<?php if ( $this->config->get('jcm_layout_date',1) == 1 ): ?>
	<span class="jcm-bullet">&middot;</span>
	<span class="jcm-date">
		<?php echo $this->cm->created_time; ?>
	</span>
	<?php endif; ?>

	<?php if($this->cm->locked):?>
		<span class="jcm-bullet">&middot;</span>
		<span class="jcm-tooltip" title="<?php echo jtext::_('JCM_PRIVATE_COMMENT') ?>"><i class="fas fa-lock"></i></span>
	<?php endif;?>

	<meta <?php echo $schema; ?>itemprop="dateCreated" content="<?php echo $this->cm->created_og; ?>" />

	<?php // reporting + collapse ?>
	<ul class="jcm-inline jcm-q-menu">
		<?php if ($this->cm->child_count>0 && $this->config->get('jcm_layout_tg_reply',1)==1 && ( $this->config->get('child_style',1)==0|| ($this->cm->parent_id==0 && $this->config->get('child_style',1)) )) : ?>
		<li>
			<a class="jcm-task-collapse jcm-tooltip" title="<?php echo JText::_("JCM_COLLAPSE"); ?>" href="#">
				<i class="fas fa-minus"></i>
			</a>
		</li>
		<li>
			<a class="jcm-task-expand jcm-tooltip" title="<?php echo JText::_("JCM_EXPAND"); ?>" href="#">
				<i class="fas fa-plus"></i>
			</a>
		</li>
		<?php endif; ?>

		<?php if ($this->config->def('u_report_comment',1)==1): ?>
		<li>
			<a class="jcm-report jcm-tooltip <?php echo @$this->cm->reported==1 ? 'active' : '' ?>" title="<?php echo JText::_("JCM_REPORT"); ?>" data-id="<?php echo $this->cm->id; ?>" href="#">
				<i class="fas fa-flag"></i>
			</a>
		</li>
		<?php endif; ?>
	</ul>
</div>

<?php if($locked):?>
	<div class="jcm-post-body locked" role="readmore"><?php echo jtext::_('JCM_COMMENT_PROTECTED') ?></div>
<?php else:?>
<div class="jcm-post-body" <?php echo $schema; ?>itemprop="text" data-style="<?php echo $this->cm->style_id ?>">
	<?=($this->config->get("child_style",1)=="1" && isset($this->cm->author_name2))?'<a href="#" class="jcm-hl" data-id="'.$this->cm->parent_id.'">@'.$this->cm->author_name2.'</a>':''?>
	<?php echo $this->cm->comment; ?>

	<?php
		if(isset($this->cm->params->map)
			&& !empty($this->cm->params->map->address)):
		$location = @$this->cm->params->map->lng . "," . @$this->cm->params->map->lat;
	?>
	<div class="jcm-map">
		<span>&#8212;</span>
		<?php if (isset($this->cm->params->map->icon) && !preg_match('/^\s*$/', $this->cm->params->map->icon)): ?>
			<img src="<?php echo $this->cm->params->map->icon ?>" style="width:16px;height:16px;display:inline;" alt="map" width="16" height="16">
		<?php endif; ?>

		<a href="#" class="jcm-map-dest" data-location="<?php echo $location; ?>" data-address="<?php echo htmlspecialchars($this->cm->params->map->address) ?>" data-img="<?=htmlspecialchars($this->cm->params->map->icon)?>">
			<?php echo isset($this->cm->params->map->name) && !preg_match('/^\s*$/', $this->cm->params->map->name) ? $this->cm->params->map->name : $this->cm->params->map->address; ?>
		</a>
	</div>
	<?php endif; ?>
</div>

<?php
	// media
	if($this->cm->media!=null):
		// images
		if(count($this->cm->media->images)):
			?>
			<ul class="jcm-images-slideshow">
			<?php
			foreach ($this->cm->media->images as $img):
				?>
				<li 
					data-src="<?php echo $img->root ?>" 
					data-sub-html="<?php echo $img->html ?>">
					<a href="#">
						<span class="t jcm-tooltip" style="background-image:url(<?=$img->thumb?>)" title="<?=$img->name?>"></span>
					</a>
				</li>
				<?php
			endforeach;
			?>
			</ul>
			<?php
		endif;

		// files
		if(count($this->cm->media->files)):
			?>
			<div class="jcm-files-attached">
				<div class="_caption"><?php echo JText::_("JCM_FILE_ATTACHED"); ?></div>
				<ul>
				<?php
				foreach ($this->cm->media->files as $file):
					?>
					<li>
						<a href="<?php echo $file->url2download ?>" class="_title">
							<i class="fas fa-arrow-alt-circle-down"></i>
							<span><?php echo $file->name ?></span>
						</a>
						<?php if (! preg_match("/^\s*$/", $file->description)): ?>
							<a href="#" class="_detail _off">
								<span class="forOff"><?php echo JText::_("JCM_DETAIL"); ?></span>
								<span class="forOn"><?php echo JText::_("JCM_HIDE"); ?></span>
							</a>
							<p class="_desc"><?php echo $file->description ?></p>
						<?php endif; ?>
					</li>
					<?php
				endforeach;
				?>
				</ul>
			</div>
			<?php
		endif;
	endif;
?>
<?php endif;?>

<?php
// admin
$extra = "";
if ( $this->config->get("u_show_ip_addr",false)==true)
{
	$extra .= '<b>'. JText::_('JCM_IP_ADDRESS') .':</b> ' . $this->cm->ip_address . '  ';
}

if ( $this->config->get("u_show_author_email",false)==true && !empty($this->cm->guest_email) && !$this->cm->created_by)
{
	$extra .= '<b>Email:</b> <a href="mailto:'.$this->cm->guest_email.'">' . $this->cm->guest_email . '</a>';
}

if ($extra != '')
{
	echo '<div class="jcm-extra-admin">'.$extra.'</div>';
}