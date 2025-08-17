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
    <?php return; ?>
<?php endif; ?>

<div id="<?php echo $uid; ?>"
     class="mod-spotlight carousel slide"
     data-bs-ride="false"
     data-bs-interval="false"
     data-bs-touch="true"
     data-bs-wrap="true">

    <div class="carousel-inner">
        <?php
        $count = count($items);
        for ($i = 0; $i < $count; $i++):
            $isActive = ($i === 0) ? ' active' : '';
            ?>
            <div class="carousel-item<?php echo $isActive; ?>">
                <div class="container-fluid">
                    <div class="row g-3 g-md-4">
                        <?php
                        $renderCard = function($idx, $extraClasses = '') use ($items) {
                            $item = $items[$idx];
                            $href = Route::_('index.php?option=com_content&view=article&id=' . (int) $item->id);
                            $img  = $item->image_intro ?: '';
                            $alt  = $item->image_intro_alt ?: $item->title;
                            ?>
                            <div class="col-12 col-sm-6 col-md-4 col-lg-3 <?php echo $extraClasses; ?>">
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

                        // Base + responsive “clones”
                        $renderCard($i);
                        if ($count > 1) { $renderCard(($i + 1) % $count, 'd-none d-sm-block'); }
                        if ($count > 2) { $renderCard(($i + 2) % $count, 'd-none d-md-block'); }
                        if ($count > 3) { $renderCard(($i + 3) % $count, 'd-none d-lg-block'); }
                        ?>
                    </div>
                </div>
            </div>
        <?php endfor; ?>
    </div>
</div>
