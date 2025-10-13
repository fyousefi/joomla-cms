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
     class="offcanvas <?php echo $sideClass; ?>"
     tabindex="-1"
     aria-labelledby="<?php echo $offId; ?>Label"
     data-bs-scroll="true">
    <div class="offcanvas-header">
        <h5 class="offcanvas-title" id="<?php echo $offId; ?>Label"><?php echo JText::_('JTOC'); ?></h5>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="<?php echo JText::_('JCLOSE'); ?>"></button>
    </div>
    <div class="offcanvas-body text-body">
        <nav aria-label="Article sections">
            <ul class="list-group list-group-flush">
                <?php foreach ($sections as $s): ?>
                    <li class="list-group-item bg-transparent border-0">
                        <a class="d-block py-2 link-body-emphasis list-group-item-action"
                           href="#<?php echo htmlspecialchars($s->id, ENT_QUOTES, 'UTF-8'); ?>">
                            <?php echo htmlspecialchars($s->title, ENT_QUOTES, 'UTF-8'); ?>
                        </a>
                    </li>
                <?php endforeach; ?>
            </ul>
        </nav>
    </div>
</div>
