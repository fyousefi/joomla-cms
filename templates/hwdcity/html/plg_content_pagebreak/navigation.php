<?php
defined('_JEXEC') or die;
/** @var array $links ['previous' => '', 'next' => ''] */
?>
<nav class="d-flex justify-content-between my-3" aria-label="Article navigation">
    <div>
        <?php if (!empty($links['previous'])): ?>
            <a class="btn btn-outline-secondary" href="<?php echo $links['previous']; ?>">&laquo; <?php echo JText::_('JPREV'); ?></a>
        <?php endif; ?>
    </div>
    <div>
        <?php if (!empty($links['next'])): ?>
            <a class="btn btn-outline-secondary" href="<?php echo $links['next']; ?>"><?php echo JText::_('JNEXT'); ?> &raquo;</a>
        <?php endif; ?>
    </div>
</nav>
