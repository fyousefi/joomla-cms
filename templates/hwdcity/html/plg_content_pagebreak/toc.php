<?php
defined('_JEXEC') or die;

/** @var array $list  each item: ->title, ->link (string|false), ->active (bool) */
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;

$doc       = Factory::getDocument();
$direction = $doc->getDirection();           // 'ltr'|'rtl'
$sideClass = ($direction === 'rtl') ? 'offcanvas-end' : 'offcanvas-start';
$offId     = 'pbTocOffcanvas';

// Find current page title
$current = '';
foreach ($list as $item) {
	if (!empty($item->active) && empty($item->link)) {
		$current = trim($item->title ?? '');
		break;
	}
}
if ($current === '' && !empty($list[0]->title)) {
	$current = trim($list[0]->title);
}

?>

<div class="text-center my-2 fw-semibold"><?php echo htmlspecialchars($current, ENT_QUOTES, 'UTF-8'); ?></div>

<div class="<?php echo ($direction === 'rtl') ? 'me-auto' : 'ms-auto'; ?> text-end">
    <button class="btn btn-outline-secondary" type="button"
            data-bs-toggle="offcanvas" data-bs-target="#<?php echo $offId; ?>">
        <span class="visually-hidden"><?php echo Text::_('JTOC'); ?></span> ☰
    </button>
</div>

<div id="<?php echo $offId; ?>" class="offcanvas <?php echo $sideClass; ?>" tabindex="-1"
     aria-labelledby="<?php echo $offId; ?>Label" data-bs-scroll="true">
    <div class="offcanvas-header">
        <h5 class="offcanvas-title" id="<?php echo $offId; ?>Label"><?php echo Text::_('JTOC'); ?></h5>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="<?php echo Text::_('JCLOSE'); ?>"></button>
    </div>
    <div class="offcanvas-body">
        <ul class="list-group list-group-flush">
			<?php foreach ($list as $item): ?>
				<?php
				$title = htmlspecialchars($item->title ?? '', ENT_QUOTES, 'UTF-8');
				$isActive = !empty($item->active) && empty($item->link);
				?>
                <li class="list-group-item bg-transparent border-0 <?php echo $isActive ? 'active' : ''; ?>">
					<?php if (!empty($item->link)): ?>
                        <a class="d-block py-2" href="<?php echo $item->link; ?>"><?php echo $title; ?></a>
					<?php else: ?>
                        <span class="d-block py-2"><?php echo $title; ?></span>
					<?php endif; ?>
                </li>
			<?php endforeach; ?>
        </ul>
    </div>
</div>
