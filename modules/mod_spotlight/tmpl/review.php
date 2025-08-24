<?php
defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Language\Text;

/** @var array $items */
/** @var \Joomla\Registry\Registry $params */
/** @var \stdClass $module */

$app      = Factory::getApplication();
$uid      = 'mod-spotlight-' . (int) $module->id; // desktop id for external controls
$emptyMsg = Text::_('MOD_SPOTLIGHT_EMPTY');

$wa = $app->getDocument()->getWebAssetManager();
$wa->useScript('bootstrap.carousel');

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
    $score = isset($item->score) && $item->score !== null && $item->score !== '' ? number_format((float)$item->score, 1) : null;
    ?>
    <article class="card h-100 border-0 bg-transparent shadow-0 position-relative">
        <a class="text-decoration-none zoom-container" href="<?php echo $href; ?>">
            <div class="ratio ratio-16x9 position-relative">
                <?php if ($img): ?>
                    <img loading="lazy" decoding="async" class="image-zoom"
                         src="<?php echo htmlspecialchars($img, ENT_QUOTES, 'UTF-8'); ?>"
                         alt="<?php echo htmlspecialchars($alt, ENT_QUOTES, 'UTF-8'); ?>">
                <?php else: ?>
                    <img loading="lazy" decoding="async"
                         src="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 9'%3E%3Crect width='16' height='9' fill='%23e9ecef'/%3E%3C/svg%3E"
                         alt="">
                <?php endif; ?>

                <?php if ($score !== null): ?>
                    <span class="score-badge position-absolute top-0 end-0 translate-middle-y me-2 mt-2 rounded-circle d-inline-flex align-items-center justify-content-center">
                        <?php echo htmlspecialchars($score, ENT_QUOTES, 'UTF-8'); ?>
                    </span>
                <?php endif; ?>
            </div>
        </a>
        <div class="card-body p-3">
            <h3 class="fs-7 ss02 m-0">
                <a class="stretched-link lh-lg fw-bold text-decoration-none link-underline-opacity-0 link-underline-opacity-25-hover" href="<?php echo $href; ?>" style="--bs-link-color:#fff; --bs-link-hover-color:var(--bs-danger);">
                    <?php echo htmlspecialchars($item->title ?? '', ENT_QUOTES, 'UTF-8'); ?>
                </a>
            </h3>
        </div>
    </article>
    <?php
};

// Generic carousel renderer (no internal controls; chrome owns them)
$renderCarousel = static function (string $id, string $displayCls, int $perSlide, string $colCls, bool $wrapFill)
use ($items, $count, $renderCard) {
    // Wrap only when we actually have enough items
    $doWrap = $wrapFill && $count >= $perSlide;

    $totalSlides = (int) ceil($count / max(1, $perSlide));
    ?>
    <div id="<?php echo $id; ?>" class="mod-spotlight carousel slide <?php echo $displayCls; ?>"
         data-bs-ride="false" data-bs-interval="false" data-bs-touch="true" data-bs-wrap="true">

        <div class="carousel-inner">
            <?php for ($s = 0; $s < $totalSlides; $s++): ?>
                <div class="carousel-item<?php echo $s === 0 ? ' active' : ''; ?>">
                    <div class="container-fluid">
                        <div class="row g-3 g-md-4">
                            <?php
                            $start = $s * $perSlide;
                            // If not wrapping, only render the remaining real items on this slide
                            $iterMax = $doWrap ? $perSlide : min($perSlide, max(0, $count - $start));

                            for ($j = 0; $j < $iterMax; $j++) {
                                $idx = $start + $j;
                                if ($idx >= $count) {
                                    if (!$doWrap) break;
                                    $idx = $idx % $count; // wrap-fill only when allowed
                                }
                                ?>
                                <div class="<?php echo $colCls; ?>">
                                    <?php $renderCard($items[$idx]); ?>
                                </div>
                                <?php
                            }
                            ?>
                        </div>
                    </div>
                </div>
            <?php endfor; ?>
        </div>
    </div>
    <?php
};
?>

<?php
// Phones: 1 per slide
$renderCarousel($uid . '-xs', 'd-block d-md-none', 1, 'col-12', false);
// Tablets: 3 per slide
$renderCarousel($uid . '-md', 'd-none d-md-block d-lg-none', 3, 'col-md-4', true);
// Desktop: 4 per slide
$renderCarousel($uid, 'd-none d-lg-block', 4, 'col-lg-3', true);
