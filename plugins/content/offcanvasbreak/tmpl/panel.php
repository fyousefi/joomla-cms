<?php
defined('_JEXEC') or die;
/** @var array $displayData */
$sections  = $displayData['sections'] ?? [];
$articleId = (int)($displayData['articleId'] ?? 0);
$sideClass = ($this->direction === 'rtl') ? 'offcanvas-end' : 'offcanvas-start';
$offId     = 'articleTocOffcanvas-' . $articleId;
if (!$sections) return;
?>
<div id="<?php echo $offId; ?>"
     class="offcanvas <?php echo $sideClass; ?> text-bg-dark"
     tabindex="-1"
     aria-labelledby="<?php echo $offId; ?>Label"
     data-bs-scroll="true">
    <div class="offcanvas-header">
        <h5 class="offcanvas-title" id="<?php echo $offId; ?>Label">فهرست مطالب</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas" aria-label="<?php echo JText::_('JCLOSE'); ?>"></button>
    </div>
    <div class="offcanvas-body text-body">
        <nav aria-label="Article sections">
            <?php $first = true; ?>
            <ul class="m-0 p-0 list-unstyled">
                <?php foreach ($sections as $s): ?>
                    <?php
                    $activeClass = $first ? ' bg-danger text-white' : ' text-light';
                    $first = false;
                    ?>
                    <li class="border-0">
                        <a class="d-block px-3 py-2 text-decoration-none fw-normal text-light <?php echo $activeClass; ?>"
                           href="#<?php echo htmlspecialchars($s->id, ENT_QUOTES, 'UTF-8'); ?>">
                            <?php echo htmlspecialchars($s->title, ENT_QUOTES, 'UTF-8'); ?>
                        </a>
                    </li>
                <?php endforeach; ?>
            </ul>
        </nav>
    </div>
</div>
