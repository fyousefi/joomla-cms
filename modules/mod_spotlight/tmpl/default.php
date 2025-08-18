<?php
defined('_JEXEC') or die;
/** @var array $items */
/** @var \Joomla\Registry\Registry $params */
/** @var \stdClass $module */

use Joomla\CMS\Factory;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Language\Text;

$app      = Factory::getApplication();
$uid      = 'mod-spotlight-' . (int) $module->id; // desktop carousel id (external controls target this)
$emptyMsg = Text::_('MOD_SPOTLIGHT_EMPTY');
$prevLbl  = Text::_('MOD_SPOTLIGHT_PREV');
$nextLbl  = Text::_('MOD_SPOTLIGHT_NEXT');

/** @var Joomla\CMS\WebAsset\WebAssetManager $wa */
$wa = $app->getDocument()->getWebAssetManager();
$wa->useScript('bootstrap.carousel');

if (empty($items)) :
    ?>
    <div class="text-muted small py-3"><?php echo $emptyMsg; ?></div>
    <?php return;
endif;

$count = count($items);

$renderCard = static function ($item) {
    $href = Route::_('index.php?option=com_content&view=article&id=' . (int) $item->id);
    $img  = $item->image_intro ?: '';
    $alt  = $item->image_intro_alt ?: $item->title;
    ?>
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
    <?php
};

/**
 * Render a carousel for a specific breakpoint.
 * $wrapFill=true wraps the final slide to keep a full row.
 */
$renderCarousel = static function (string $id, string $displayCls, int $perSlide, string $colCls, bool $wrapFill, bool $withControls)
use ($items, $count, $renderCard, $prevLbl, $nextLbl) {
    $totalSlides = (int) ceil($count / $perSlide);
    ?>
    <div id="<?php echo $id; ?>"
         class="mod-spotlight carousel slide <?php echo $displayCls; ?>"
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
                            $start = $s * $perSlide;
                            for ($j = 0; $j < $perSlide; $j++) {
                                $idx = $start + $j;
                                if ($idx >= $count) {
                                    if (!$wrapFill) break;
                                    $idx = $idx % $count; // wrap-fill
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

        <?php if ($withControls && $totalSlides > 1): ?>
            <button class="carousel-control-prev" type="button" data-bs-target="#<?php echo $id; ?>" data-bs-slide="prev" aria-label="<?php echo $prevLbl; ?>">
                <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                <span class="visually-hidden"><?php echo $prevLbl; ?></span>
            </button>
            <button class="carousel-control-next" type="button" data-bs-target="#<?php echo $id; ?>" data-bs-slide="next" aria-label="<?php echo $nextLbl; ?>">
                <span class="carousel-control-next-icon" aria-hidden="true"></span>
                <span class="visually-hidden"><?php echo $nextLbl; ?></span>
            </button>
        <?php endif; ?>
    </div>
    <?php
};
?>

<?php
// Phones (xs & sm): 1 item per slide — NO internal controls
$renderCarousel($uid . '-xs', 'd-block d-md-none', 1, 'col-12', false, false);

// Tablets (md only): 3 items per slide — NO internal controls
$renderCarousel($uid . '-md', 'd-none d-md-block d-lg-none', 3, 'col-md-4', true, false);

// Desktop (lg+): 4 items per slide — NO internal controls (external chrome controls are used)
$renderCarousel($uid, 'd-none d-lg-block', 4, 'col-lg-3', true, false);
