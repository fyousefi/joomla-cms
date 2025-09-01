<?php
/**
 * @package     JLex Comment
 * @version     1.0.0
 * @copyright   Copyright (C) 2013-2016 JLexArt Team (http://www.jlexart.com). All rights reserved.
 * @license     GNU/GPL or later
 * @author      JLexArt (support@jlexart.com)
 */

defined ( '_JEXEC' ) or die ();
?>

<div id="comment"></div>

<?php echo JLexCommentHelper::loadModules("jcm-top"); ?>

<div id="jlexcomment">
	<div id="jcm-header">
		<?php echo $this->loadTemplate("header"); ?>

		<!-- Subscribe/Sort by -->
		<ul class="jcm-top-secondary jcm-inline">
			<?php if ($this->config->def('u_sub_comment',1)==1): ?>
			<li>
				<a href="#" class="jcm-subscribe <?php echo $this->user->get('jcm_subscribe',false)==true ? '_on' : '_off' ?>">
					<i class="far fa-envelope"></i>
					<span style="display:inline-block">
						<span class="forOff"><?php echo JText::_("JCM_SUBSCRIBE"); ?></span>
						<span class="forOn"><?php echo JText::_("JCM_UNSUBSCRIBE"); ?></span>
					</span>
				</a>
			</li>
			<?php endif; ?>

			<?php
				if ($this->config->def('jcm_layout_sort',1)==1):
					$sortlist = array (
							'best' 		=> JText::_('JCM_BEST'),
							'popular' 	=> JText::_('JCM_POPULAR'),
							'desc'		=> JText::_('JCM_NEWEST'),
							'asc' 		=> JText::_('JCM_OLDEST')
						);
			?>
			<li class="jcm-pull-right">
				<div class="jcm-dropdown">
					<a href="#" class="jcm-dropdown-toggle">
						<span class="jcm-dropdown-val"><?php echo $sortlist [$this->config->def('sort','best')] ?></span>
						<i class="fas fa-angle-down"></i>
					</a>
					<ul class="jcm-dropdown-menu jcm-sortby">
						<?php foreach ($sortlist as $key => $sort): ?>
							<li>
								<a href="#" class="_dx <?php echo $this->config->def('sort','best')==$key ? 'active' : '' ?>" data-sort="<?php echo $key; ?>"><?php echo $sort; ?></a>
							</li>
						<?php endforeach; ?>
					</ul>
				</div>
			</li>
			<?php endif; ?>

			<?php if ($this->config->get('collapse_all',1)==1): ?>
			<li class="jcm-pull-right" style="margin-right:10px">
				<a href="#" class="jcm-toggle-all _on">
					<span class="forOn"><?php echo jtext::_('JCM_COLLAPSE_ALL') ?> <i class="fas fa-compress-alt"></i></span>
					<span class="forOff"><?php echo jtext::_('JCM_EXPAND_ALL') ?> <i class="fas fa-expand-alt"></i></span>
				</a>
			</li>
			<?php endif;?>
		</ul>
	</div>

	<?php if($this->data->object->get("readonly",0)):?>
		<div class="jcm_form_error active">
			<?php echo jtext::_("JCM_READ_ONLY_DESC_FRONT") ?>
		</div>
	<?php endif;?>

	<?php if($this->config->get('form_possition','top')=='top' && !$this->data->object->get("readonly",0)) : ?>
	<div class="jcm-form-cp" id="jcm-form">
		<?php echo $this->loadTemplate("form"); ?>
	</div>
	<?php endif; ?>


	<?php if ( $this->config->def('jcm_layout_ppl',1)==1 && count($this->peoples)): ?>
	<div>
		<span class="jcm-h6"><?php echo JText::_("JCM_PEOPLE_CONVERSATION_TITLE"); ?></span>
		<ul class="jcm-inline">
			<?php foreach ($this->peoples as $people): ?>
			<li>
				<a 
				<?php if (!empty($people->link)): ?>
					class="jcm-tooltip" href="<?php echo $people->link ?>"
				<?php else: ?>
					class="jcm-tooltip jcm-user-cm" data-id="<?php echo $people->id ?>"
				<?php endif; ?>
				 title="<?php echo $people->name; ?>" href="#">
					<img class="jcm-thumb-small" src="<?php echo $people->thumb ?>" alt="<?php echo $people->name ?>" <?php echo preg_match('/jcm/', $people->thumb)?'width="120" height="120"':'' ?> />
				</a>
			</li>
			<?php endforeach; ?>
		</ul>
	</div>
	<?php endif; ?>

	<div class="jcm-refresh">
		<span class="_sw0">
			<?php echo JText::_("JCM_LOADING_COMMENT"); ?>
		</span>
		<span class="_sw1">
			<?php echo JText::_("JCM_REFRESH_CM_MIN"); ?> <span class="jcm-refresh-sub">00:00</span>.
		</span>
	</div>

	<div id="jcm-comments" class="<?php echo $this->config->get('cache',1)?'':'hide hidden' ?>">
		<?php
			if(!empty($this->data->comments) && is_array($this->data->comments) && count($this->data->comments))
			{
				echo $this->loadTemplate("comments");
			} else {
				if ($this->data->page==1)
				{
					// No comment.
					echo '<p class="jcm-center jcm-empty-cm">';
					echo JText::_("JCM_BE_THE_FIRST_TO_COMMENT");
					echo '</p>';
				}
			}
		?>
	</div>

	<?php if($this->config->get('form_possition','top')=='bottom' && !$this->data->object->get("readonly",0)): ?>
	<div class="jcm-form-cp" id="jcm-form">
		<?php echo $this->loadTemplate("form"); ?>
	</div>
	<?php endif; ?>

</div>

<div id="jcm-emoji"></div>

<script type="text/javascript">
(function($)
{
	<?php if($this->config->get('jquery_ready',1)==1):?>
	$(document).ready(function(){
	<?php endif;?>
		var jlexcm = new jlexcomment($, <?=json_encode($this->js['cf'])?>, <?=json_encode($this->js['settings'])?>);

		jlexcm.init();
	<?php if($this->config->get('jquery_ready',1)==1):?>
	})
	<?php endif;?>
})(jQuery);
</script>