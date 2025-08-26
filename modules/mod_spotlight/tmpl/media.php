<?php
\defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text; // J4+
use Joomla\CMS\Router\Route;
use Joomla\Component\Content\Site\Helper\RouteHelper;

/** @var array $items */
/** @var \Joomla\Registry\Registry $params */
/** @var \stdClass $module */

$app      = Factory::getApplication();
$emptyMsg = Text::_('MOD_SPOTLIGHT_EMPTY');

if (empty($items)) {
    echo '<div class="text-muted small py-3">' . $emptyMsg . '</div>';
    return;
}

/* Fixed 5 items: 1 hero + 4 small (if fewer exist, we render what we have) */
$items = array_values($items);
$items = \array_slice($items, 0, 5);
$count = \count($items);

$imgTag = static function ($src, $alt) {
    if ($src) {
        return '<img loading="lazy" decoding="async" class="image-zoom" src="' .
            htmlspecialchars($src, ENT_QUOTES, 'UTF-8') . '" alt="' .
            htmlspecialchars($alt, ENT_QUOTES, 'UTF-8') . '">';
    }
    return '<img loading="lazy" decoding="async" src="data:image/svg+xml,%3Csvg xmlns=\'http://www.w3.org/2000/svg\' viewBox=\'0 0 16 9\'%3E%3Crect width=\'16\' height=\'9\' fill=\'%23e9ecef\'/%3E%3C/svg%3E" alt="">';
};

$card = static function ($item, bool $isHero = false) use ($imgTag) {
    $href     = Route::_('index.php?option=com_content&view=article&id=' . (int) $item->id);
    $img      = $item->image_intro ?? '';
    $alt      = $item->image_intro_alt ?? ($item->title ?? '');
    $catTitle = ($item->category_title ?? '');
    $catId    = ($item->catid ?? 0);
    $catLink  = (Route::_(RouteHelper::getCategoryRoute($item->catid)) ?? 0);
    $date     = $item->created ?? '';
    ?>
    <article class="<?php echo $isHero ? 'media-hero' : 'media-small'; ?> position-relative">
        <a class="text-decoration-none zoom-container d-block position-relative" href="<?php echo $href; ?>">
            <div class="ratio ratio-16x9">
                <?php echo $imgTag($img, $alt); ?>

                <?php
                if ($catTitle !== '' && $catId > 0): ?>
                <div class="position-absolute top-0 end-0 m-2 z-2">
                    <a href="<?php echo $catLink; ?>"
                       class="badge text-bg-danger text-decoration-none fw-normal">
                        <?php echo htmlspecialchars($catTitle, ENT_QUOTES, 'UTF-8'); ?>
                    </a>
                </div>
                <?php endif; ?>

                <!-- centered play mark -->
                <span class="media-play position-absolute top-50 start-50 translate-middle d-inline-flex align-items-center justify-content-center">
                    <i class="fa fa-play"></i>
                </span>
            </div>
        </a>

        <div class="pt-2">
            <h3 class="fs-7 ss02 m-0<?php echo $isHero ? ' fw-bold' : ''; ?>">
                <a class="stretched-link lh-lg fw-bold text-decoration-none"
                   style="--bs-link-color:#fff; --bs-link-hover-color:var(--bs-danger);"
                   href="<?php echo $href; ?>">
                    <?php echo htmlspecialchars($item->title ?? '', ENT_QUOTES, 'UTF-8'); ?>
                </a>
            </h3>
            <?php if ($date): ?>
                <div class="text-body-secondary small mt-1">
                    <i class="fa-regular fa-calendar ms-1"></i>
                    <?php echo HTMLHelper::_('date', $date, 'd F Y'); ?>
                </div>
            <?php endif; ?>
        </div>
    </article>
    <?php
};
?>

<div class="container-fluid">
    <!-- vertical stack: hero then strip -->
    <div class="media-grid d-flex flex-column gap-3 gap-md-4">

        <!-- HERO (first item) -->
        <?php $card($items[0], true); ?>

        <!-- STRIP: up to 4 items in ONE row on lg+ -->
        <?php if ($count > 1): ?>
            <div class="media-strip d-flex flex-wrap gap-3 gap-md-4">
                <?php for ($i = 1; $i < $count; $i++): ?>
                    <div class="media-col">
                        <?php $card($items[$i], false); ?>
                    </div>
                <?php endfor; ?>
            </div>
        <?php endif; ?>

    </div>
</div>
