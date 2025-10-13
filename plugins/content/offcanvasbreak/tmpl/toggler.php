<?php
defined('_JEXEC') or die;
/** @var array $displayData */
$articleId = (int)($displayData['articleId'] ?? 0);
$offId     = 'articleTocOffcanvas-' . $articleId;
$currentTitle = $displayData['sections'][0]->title ?? ($displayData['title'] ?? '');
?>
<div class="d-flex align-items-center my-2">
    <button class="btn btn-outline-secondary me-2"
            type="button"
            data-bs-toggle="offcanvas"
            data-bs-target="#<?php echo $offId; ?>"
            aria-controls="<?php echo $offId; ?>">
        <span class="visually-hidden"><?php echo JText::_('JTOC'); ?></span> ☰
    </button>
    <div class="fw-semibold text-center flex-grow-1 text-white">
        <?php echo htmlspecialchars($currentTitle, ENT_QUOTES, 'UTF-8'); ?>
    </div>
</div>
