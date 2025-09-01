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

<?php if (isset($this->heading) && $this->heading!=null):?>
	<div class="page-header">
		<h2><?php echo $this->heading ?></h2>
	</div>
<?php endif; ?>

<div class="user-comments">
<?php if ($this->activity->comments): ?>
	<?php foreach ($this->activity->comments as $comment): ?>
		<div class="uc-item">
			<span class="uc-author">
				<b><?php echo $this->activity->user->username; ?></b>
			</span>

			<span class="uc-action">
				<?php echo ($comment->parent_id>0) ? JText::_("JCM_REPLIED_A_COMMENT") : JText::_("JCM_COMMENT") ?>
			</span>

			<?php echo ' ' . JText::_("JCM_IN_LOWER") . ' '; ?>

			<a href="<?php echo $comment->object_url; ?>"><b><?php echo $comment->object_name; ?></b></a>

			<span class="uc-date">
				<?php echo $comment->created_time ?>
			</span>

			<div class="uc-comment"><?php echo $comment->comment ?></div>
		</div>
	<?php endforeach; ?>

	<div class="uc-pagination">
		<?php echo $this->activity->pagination->getListFooter(); ?>
	</div>
<?php else: ?>
	<div class="uc-empty"><?php echo JText::_("JCM_NO_COMMENT"); ?></div>
<?php endif; ?>
</div>