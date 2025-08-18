<?php
defined('_JEXEC') or die;
/** @var array $items */
/** @var \Joomla\Registry\Registry $params */
/** @var \stdClass $module */

use Joomla\CMS\Factory;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Language\Text;

$app       = Factory::getApplication();
$uid       = 'mod-spotlight-' . (int) $module->id;
$prevLabel = Text::_('MOD_SPOTLIGHT_PREV');
$nextLabel = Text::_('MOD_SPOTLIGHT_NEXT');
$emptyMsg  = Text::_('MOD_SPOTLIGHT_EMPTY');

/** @var Joomla\CMS\WebAsset\WebAssetManager $wa */
$wa = $app->getDocument()->getWebAssetManager();
$wa->useScript('bootstrap.carousel');

if (empty($items)) :
    ?>
    <div class="text-muted small py-3"><?php echo $emptyMsg; ?></div>
    <?php return;
endif;

$count     = count($items);
$perSlide  = 4;                                // 4 items per desktop slide
$totalSlides = (int) ceil($count / $perSlide); // step by 4
$colsThisSlide = min($perSlide, $count);       // never duplicate within a slide if total < 4

// Card renderer (keeps your Bootstrap grid + classes)
$renderCard = static function (int $idx, string $extraClasses = '') use ($items) {
    $item = $items[$idx];
    $href = Route::_('index.php?option=com_content&view=article&id=' . (int) $item->id);
    $img  = $item->image_intro ?: '';
    $alt  = $item->image_intro_alt ?: $item->title;
    ?>
    <div class="col-12 col-sm-6 col-md-4 col-lg-3<?php echo $extraClasses ? ' ' . $extraClasses : ''; ?>">
        <article class="card h-100 border-0">
            <a class="text-decoration-none" href="<?php echo $href; ?>">
                <div class="ratio ratio-16x9">
                    <?php if ($img): ?>
                        <img loading="lazy" decoding="async"
                             src="<?php echo htmlspecialchars($img, ENT_QUOTES, 'UTF-8'); ?>"
                             alt="<?php echo htmlspecialchars($alt, ENT_QUOTES, 'UTF-8'); ?>">
                    <?php else: ?>
                        <img loading="lazy" decoding="async"
                             src="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 9'%3E%3Crect width='16' height='9' fill='%23e9ecef'/%3E%3C/svg%3E"
                             alt="">
                    <?php endif; ?>
                </div>
            </a>
            <div class="card-body p-3">
                <h3 class="fs-7 ss02">
                    <a class="stretched-link lh-lg fw-bold text-decoration-none" href="<?php echo $href; ?>">
                        <?php echo htmlspecialchars($item->title, ENT_QUOTES, 'UTF-8'); ?>
                    </a>
                </h3>
            </div>
        </article>
    </div>
    <?php
};

// Visibility helper for 1/2/3/4 items at xs/sm/md/lg
$visClass = static function (int $j): string {
    return $j === 0 ? '' :
        ($j === 1 ? ' d-none d-sm-block' :
            ($j === 2 ? ' d-none d-md-block' :
                ($j === 3 ? ' d-none d-lg-block' : '')));
};
?>

<div id="<?php echo $uid; ?>"
     class="mod-spotlight carousel slide"
     data-bs-ride="false"
     data-bs-interval="false"
     data-bs-touch="true"
     data-bs-wrap="true">

    <div class="carousel-inner">
        <?php for ($s = 0; $s < $totalSlides; $s++): ?>
            <div class="carousel-item<?php echo $s === 0 ? ' active' : ''; ?>">
                <div class="container-fluid">
                    <div class="row g-3 g-md-4">
                        <?php
                        // Start index for this slide: 0, 4, 8, 12, ...
                        $start = $s * $perSlide;

                        // Output exactly 1/2/3/4 columns using modulo to wrap ONLY on the final slide
                        for ($j = 0; $j < $colsThisSlide; $j++) {
                            $idx = ($start + $j) % $count;       // wrap after last item
                            $renderCard($idx, $visClass($j));     // show extra cols at sm/md/lg
                        }
                        ?>
                    </div>
                </div>
            </div>
        <?php endfor; ?>
    </div>
</div>
