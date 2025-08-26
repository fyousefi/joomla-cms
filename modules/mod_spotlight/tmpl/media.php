<?php
defined('_JEXEC') or die;

use Joomla\CMS\Router\Route;
use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Factory;

/** @var array $items */
/** @var \Joomla\Registry\Registry $params */
/** @var \stdClass $module */

if (empty($items)) {
    echo '<div class="text-muted small py-3">' . Text::_('MOD_SPOTLIGHT_EMPTY') . '</div>';
    return;
}

$fmt = 'd F Y'; // e.g., 05 September 2025 (use locale)
$first = $items[0];
$rest  = array_slice($items, 1, 4);

$href = static function ($id) {
    return Route::_('index.php?option=com_content&view=article&id=' . (int) $id);
};

$imgTag = static function ($src, $alt) {
    if ($src) {
        return '<img loading="lazy" decoding="async" class="image-zoom" src="' .
            htmlspecialchars($src, ENT_QUOTES, 'UTF-8') . '" alt="' .
            htmlspecialchars($alt, ENT_QUOTES, 'UTF-8') . '">';
    }
    return '<img loading="lazy" decoding="async" src="data:image/svg+xml,%3Csvg xmlns=\'http://www.w3.org/2000/svg\' viewBox=\'0 0 16 9\'%3E%3Crect width=\'16\' height=\'9\' fill=\'%23e9ecef\'/%3E%3C/svg%3E" alt="">';
};
?>

<div class="media-spotlight">
    <!-- Big lead item -->
    <div class="mb-3">
        <a class="text-decoration-none zoom-container d-block position-relative" href="<?php echo $href($first->id); ?>">
            <div class="ratio ratio-16x9">
                <?php echo $imgTag($first->image_intro ?? '', $first->image_intro_alt ?? ($first->title ?? '')); ?>

                <!-- category badge -->
                <?php if (!empty($first->cat_title)) : ?>
                    <span class="badge bg-danger position-absolute top-0 start-0 m-2 rounded-1 small">
                        <?php echo htmlspecialchars($first->cat_title, ENT_QUOTES, 'UTF-8'); ?>
                    </span>
                <?php endif; ?>

                <!-- play circle -->
                <span class="media-play position-absolute top-50 start-50 translate-middle d-inline-flex align-items-center justify-content-center">
                    <i class="fa fa-play"></i>
                </span>
            </div>
        </a>
        <h3 class="fs-5 mt-2 mb-1">
            <a class="text-decoration-none fw-semibold" style="--bs-link-color:#fff; --bs-link-hover-color:var(--bs-danger);" href="<?php echo $href($first->id); ?>">
                <?php echo htmlspecialchars($first->title ?? '', ENT_QUOTES, 'UTF-8'); ?>
            </a>
        </h3>
        <div class="text-body-secondary small">
            <i class="fa-regular fa-calendar ms-1"></i>
            <?php echo HTMLHelper::_('date', $first->created ?? '', $fmt); ?>
        </div>
    </div>

    <!-- Four small items -->
    <?php if ($rest): ?>
        <div class="row g-3">
            <?php foreach ($rest as $it): ?>
                <div class="col-6 col-lg-3">
                    <a class="text-decoration-none zoom-container d-block position-relative" href="<?php echo $href($it->id); ?>">
                        <div class="ratio ratio-16x9">
                            <?php echo $imgTag($it->image_intro ?? '', $it->image_intro_alt ?? ($it->title ?? '')); ?>

                            <?php if (!empty($it->cat_title)) : ?>
                                <span class="badge bg-danger position-absolute top-0 start-0 m-2 rounded-1 small">
                                    <?php echo htmlspecialchars($it->cat_title, ENT_QUOTES, 'UTF-8'); ?>
                                </span>
                            <?php endif; ?>

                            <span class="media-play position-absolute top-50 start-50 translate-middle d-inline-flex align-items-center justify-content-center">
                                <i class="fa fa-play"></i>
                            </span>
                        </div>
                    </a>
                    <h4 class="fs-7 mt-2 mb-1">
                        <a class="text-decoration-none fw-semibold" style="--bs-link-color:#fff; --bs-link-hover-color:var(--bs-danger);" href="<?php echo $href($it->id); ?>">
                            <?php echo htmlspecialchars($it->title ?? '', ENT_QUOTES, 'UTF-8'); ?>
                        </a>
                    </h4>
                    <div class="text-body-secondary small mb-1">
                        <i class="fa-regular fa-calendar ms-1"></i>
                        <?php echo HTMLHelper::_('date', $it->created ?? '', $fmt); ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>
