<?php
defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Language\Text;
use Joomla\Component\Content\Site\Helper\RouteHelper;
use Joomla\CMS\HTML\HTMLHelper;

/** @var array $items */
/** @var \Joomla\Registry\Registry $params */
/** @var \stdClass $module */

$app      = Factory::getApplication();
$uid      = 'mod-spotlight-' . (int) $module->id; // desktop id for external controls
$emptyMsg = Text::_('MOD_SPOTLIGHT_EMPTY');

if (empty($items)) : ?>
    <div class="text-muted small py-3"><?php echo $emptyMsg; ?></div>
    <?php return;
endif;

$count = count($items);

// Card
$renderCard = static function ($item) {
    $href = Route::_('index.php?option=com_content&view=article&id=' . (int) $item->id);
    $img  = $item->image_intro ?? '';
    $alt  = $item->image_intro_alt ?? ($item->title ?? '');
    $catTitle = ($item->category_title ?? '');
    $catId    = ($item->catid ?? 0);
    $catLink  = (Route::_(RouteHelper::getCategoryRoute($item->catid)) ?? 0);
    $date     = $item->created ?? '';
    ?>
    <article class="card h-100 border-0 bg-transparent shadow-0 position-relative">
        <?php if ($catTitle !== '' && $catId > 0): ?>
            <div class="position-absolute top-0 end-0 m-2 z-2">
                <a href="<?php echo $catLink; ?>"
                   class="badge text-bg-danger text-decoration-none fw-normal">
                    <?php echo htmlspecialchars($catTitle, ENT_QUOTES, 'UTF-8'); ?>
                </a>
            </div>
        <?php endif; ?>
        <a class="text-decoration-none zoom-container zoom-dark" href="<?php echo $href; ?>">
            <div class="ratio ratio-16x9 position-relative">
                <!-- centered play mark -->
                <span class="spot-media-play fa-3x position-absolute top-50 start-50 translate-middle d-inline-flex align-items-center justify-content-center">
                    <i class="fa-regular fa-circle-play"></i>
                </span>
                <?php if ($img): ?>
                    <img loading="lazy" decoding="async" class="image-zoom"
                         src="<?php echo htmlspecialchars($img, ENT_QUOTES, 'UTF-8'); ?>"
                         alt="<?php echo htmlspecialchars($alt, ENT_QUOTES, 'UTF-8'); ?>">
                <?php else: ?>
                    <img loading="lazy" decoding="async"
                         src="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 9'%3E%3Crect width='16' height='9' fill='%23e9ecef'/%3E%3C/svg%3E"
                         alt="">
                <?php endif; ?>
            </div>
        </a>
        <div class="card-body px-0">
            <h3 class="fs-7 ss02 m-0">
                <a class="stretched-link lh-lg fw-bold text-decoration-none" href="<?php echo $href; ?>" style="--bs-link-color:#fff; --bs-link-hover-color:var(--bs-danger);">
                    <?php echo htmlspecialchars($item->title ?? '', ENT_QUOTES, 'UTF-8'); ?>
                </a>
            </h3>
        </div>
        <?php if (!empty($date)): ?>
            <div class="text-white-50 pb-2 fs-10 ss02">
                <i class="fa-regular fa-calendar ms-1"></i>
                <?= HTMLHelper::_('date', $date, 'd F Y'); ?>
            </div>
        <?php endif; ?>
    </article>
    <?php
};
?>

<!-- HERO ROW (first item full width) -->
<div class="row g-3 g-md-4">
    <div class="col-12 col-lg-12">
        <?php $renderCard($items[0]); ?>
    </div>
</div>

<!-- GRID ROW (rest of items: 1-col xs, 3-col md, 4-col lg) -->
<div class="row g-3 g-md-4">
    <?php foreach (array_slice($items, 1) as $item): ?>
        <div class="col-12 col-md-4 col-lg-3">
            <?php $renderCard($item); ?>
        </div>
    <?php endforeach; ?>
</div>
