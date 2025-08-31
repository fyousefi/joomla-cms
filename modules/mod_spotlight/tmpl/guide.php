<?php
defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Router\Route;

/** @var array $items */
/** @var \Joomla\Registry\Registry $params */
/** @var \stdClass $module */

$app = Factory::getApplication();

if (empty($items)) : ?>
    <div class="text-muted small py-3"><?php echo JText::_('MOD_SPOTLIGHT_EMPTY'); ?></div>
    <?php return;
endif;
?>

<!-- Responsive grid -->
<div class="row row-cols-2 row-cols-sm-3 row-cols-md-4 g-0">
    <?php foreach ($items as $item):
        $href  = Route::_('index.php?option=com_content&view=article&id=' . (int) $item->id);
        $label = trim((string) ($item->title ?? '')) ?: ('#' . (int) $item->id);
        $icon  = trim((string) ($item->icon ?? 'fa-regular fa-square'));
        $accent = trim((string) ($item->badge_color ?? ($item->color ?? '')));
        if ($accent === '') { $accent = '#0d6efd'; }
        ?>
        <div class="col-lg-6">
            <a href="<?php echo $href; ?>"
               class="guide-tile h-100"
               style="--accent: <?php echo htmlspecialchars($accent, ENT_QUOTES, 'UTF-8'); ?>;">
                <div class="card-body d-flex flex-column align-items-center justify-content-center gap-2 py-4">
                    <i class="fs-2 <?php echo htmlspecialchars($icon, ENT_QUOTES, 'UTF-8'); ?>"
                       aria-hidden="true"></i>

                    <span class="fw-normal text-truncate fs-13">
            <?php echo htmlspecialchars($label, ENT_QUOTES, 'UTF-8'); ?>
          </span>
                </div>
            </a>
        </div>
    <?php endforeach; ?>
</div>
