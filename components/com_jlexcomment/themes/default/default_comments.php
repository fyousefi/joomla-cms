<?php
/**
 * @package     JLex Comment
 * @version     1.0.0
 * @copyright   Copyright (C) 2013-2016 JLexArt Team (http://www.jlexart.com). All rights reserved.
 * @license     GNU/GPL or later
 * @author      JLexArt (support@jlexart.com)
 */

defined ( '_JEXEC' ) or die ();

$this->parseComments = function ($comments, $level=1, $wrap=true)
{
?>
	<?php if ($wrap): ?>
	<ul class="<?=$level>1?"jcm-childs":"jcm-root"?> jcm-level-<?php echo $level?>">
	<?php endif; ?>

	<?php foreach ($comments as $cm):
		$schema = $this->config->def('schema',1)==1 ? '' : 'data-';
		?>
		<li id="comment-<?php echo $cm->id ?>" class="jcm-block jcm-expand <?php echo $cm->published==0 ? 'jcm-unpublished' : '' ?> <?php echo $cm->featured==1 ? 'jcm-featured' : '' ?>" data-parent-id="<?php echo $cm->parent_id ?>" data-id="<?php echo $cm->id ?>">
			<div class="jcm-unpublish-msg">
				<?php echo JText::_("JCM_COMMENT_UNPUBLISHED"); ?>
			</div>

			<?php if ( $this->config->def('author_thumb',1)==1 ): ?>
			<div class="avatar hovercard<?php echo $cm->isOnline?' isOnline':'' ?>">
				<a href="#" class="jcm-user-cm" data-id="<?php echo $cm->created_by ?>">
					<img src="<?php echo $cm->author_thumb ?>" alt="<?php echo $cm->author_name ?>" <?php echo preg_match('/jcm/', $cm->author_thumb)?'width="120" height="120"':'width="64" height="64"' ?> />
				</a>
			</div>
			<?php endif; ?>

			<div class="jcm-post-content">
				<div class="jcm-flex-content" <?php echo $schema; ?>itemscope <?php echo $schema; ?>itemtype="http://schema.org/Comment">
					<?php
						$this->cm = $cm;
						echo $this->loadTemplate ("comment");
					?>
				</div>

				<div class="jcm-post-footer">
					<!-- voting/reply/share -->
					<ul class="jcm-inline jcm-task-list">
						<?php if ($this->config->get('jcm_layout_vote',1)==1 && $this->config->def('u_vote_comment',1)==1): ?>
						<li class="jcm-voting" data-role="voting" data-id="<?php echo $cm->id; ?>">
							<?php if ($this->config->get('jcm_reaction',0)==0): ?>
								<span class="jcm-vote-up <?php echo $cm->up_point>0 ? '' : 'count-0' ?>" data-action="upvote" title="<?php echo JText::_("JCM_VOTE_UP"); ?>">
									<a href="#" class="jcm-count" data-role="likes"><?php echo $cm->up_point ?></a>
									<a href="#" class="jcm-control <?php echo $cm->voted==1?'active':'' ?>"><?php echo JText::_("JCM_UP"); ?></a>
								</span>
								<span class="jcm-vote-down <?php echo $cm->down_point>0 ? '' : 'count-0' ?>" data-action="downvote" title="<?php echo JText::_("JCM_VOTE_DOWN"); ?>">
									<a href="#" class="jcm-count" data-role="likes"><?php echo $cm->down_point ?></a>
									<a href="#" class="jcm-control <?php echo $cm->voted==-1?'active':'' ?>"><?php echo JText::_("JCM_DOWN"); ?></a>
								</span>
							<?php else: ?>
								<span class="jcm-reaction-data" style="display:none"><?php echo json_encode($cm->reaction) ?></span>
							<?php endif; ?>
						</li>
						<?php endif; ?>

						<?php if ( $this->config->get('u_reply_comment') == true ): ?>
						<li class="jcm-reply" data-role="reply" data-pid="<?php echo $cm->id; ?>" data-root="<?php echo $cm->root_parent_id ?>">
							<a href="#">
								<span><?php echo JText::_("JCM_REPLY"); ?></span>
							</a>
						</li>
						<?php endif; ?>

						<?php if ($this->config->get('jcm_layout_sharing',1)==1): ?>
						<li class="jcm-share"
							data-id="<?php echo $cm->id; ?>"
							data-fb="<?php echo $this->config->get('jcm_layout_sharing_fb',1)==1; ?>"
							data-tw="<?php echo $this->config->get('jcm_layout_sharing_tw',1)==1; ?>"
							data-gg="<?php echo $this->config->get('jcm_layout_sharing_gg',1)==1; ?>">
							<a href="#">
								<span><?php echo JText::_("JCM_SHARE"); ?></span>
							</a>
						</li>
						<?php endif; ?>

						<?php
							$setting = (
									($this->config->get('u_edit_any_comment')==true || ($cm->me && $this->config->get('u_edit_own_comment')==true)) ||
									($this->config->get('u_del_any_comment')==true || ($cm->me && $this->config->get('u_del_own_comment')==true)) ||
									$this->config->get('u_state_any_comment')==true ||
									$this->config->get('u_feature_any_comment')==true
								) ? true : false;
							if ( $setting==true ) :
						?>
						<li class="jcm-sig-tasks">
							<div class="jcm-dropdown">
								<a href="#" class="jcm-dropdown-toggle">
									<span><?php echo JText::_("JCM_SETTING"); ?></span>
									<i class="fas fa-angle-down"></i>
								</a>
								<ul class="jcm-dropdown-menu" data-id="<?php echo $cm->id; ?>">
									<?php if ($this->config->get('u_edit_any_comment')==true || ($cm->me && $this->config->get('u_edit_own_comment')==true)): ?>
									<li class="jcm-edit">
										<a href="#" class="_dx"><?php echo JText::_("JCM_EDIT"); ?></a>
									</li>
									<?php endif; ?>

									<?php if ($this->config->get('u_del_any_comment')==true || ($cm->me && $this->config->get('u_del_own_comment')==true)): ?>
									<li class="jcm-delete">
										<a href="#" class="_dx"><?php echo JText::_("JCM_DELETE"); ?></a>
									</li>
									<?php endif; ?>

									<?php if ($this->config->get('u_state_any_comment')==true): ?>
									<li class="jcm-state <?php echo $cm->published==1 ? '_off' : '_on' ?>">
										<a href="#" class="_dx forOn"><?php echo JText::_("JCM_PUBLISH"); ?></a>
										<a href="#" class="_dx forOff"><?php echo JText::_("JCM_UNPUBLISH"); ?></a>
									</li>
									<?php endif; ?>

									<?php if ($this->config->get('u_feature_any_comment')==true): ?>
									<li class="jcm-feature <?php echo $cm->featured==1 ? '_off' : '_on' ?>">
										<a href="#" class="_dx forOn"><?php echo JText::_("JCM_FEATURE"); ?></a>
										<a href="#" class="_dx forOff"><?php echo JText::_("JCM_UNFEATURE"); ?></a>
									</li>
									<?php endif; ?>
								</ul>
							</div>
						</li>
						<?php endif; ?>
					</ul>
				</div>

				<?php if ($cm->child_count>0): ?>
				<div class="jcm-list-reply">
					<?php
						$this->parseComments($cm->childs->comments, $level+1);

						// pagination
						if ($cm->childs->pagination!=null)
						{
							echo $cm->childs->pagination->child();
						}
					?>
				</div>
				<?php endif; ?>
			</div>
		</li>
	<?php endforeach; ?>

	<?php if( $wrap ): ?>
	</ul>
	<?php endif; ?>

<?php
};

$this->parseComments ($this->data->comments, ($this->data->pid>0?2:1), @$this->unwrap==true? false : true);

// pagination: Make sure that root id (parent_id=0)
if ($this->data->pagination!=null && $this->data->pid==0 && @$this->unwrap!=true)
{
	echo $this->data->pagination->init()->toHtml (JUri::base() . $this->data->object->url);
}