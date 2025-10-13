<?php
defined('_JEXEC') or die;
/** @var array $displayData */
$articleId = (int)($displayData['articleId'] ?? 0);
$offId     = 'articleTocOffcanvas-' . $articleId;
$currentTitle = ($displayData['title'] ?? '') ?? $displayData['sections'][0]->title;
?>
<div class="d-flex align-items-center">
    <div class="fw-semibold text-center flex-grow-1 text-white fs-13 d-none">
        <?php echo htmlspecialchars($currentTitle, ENT_QUOTES, 'UTF-8'); ?>
    </div>
    <a class="link-light text-decoration-none fs-8" href="#"
            data-bs-toggle="offcanvas"
            data-bs-target="#<?php echo $offId; ?>"
            aria-controls="<?php echo $offId; ?>">
            <span class="fw-bold fs-8">فهرست مطالب</span>
        <i class="fa-solid fa-bars fs-5 align-middle ms-1" aria-hidden="true"></i>
        <span class="visually-hidden">فهرست مطالب</span>
    </a>
</div>
