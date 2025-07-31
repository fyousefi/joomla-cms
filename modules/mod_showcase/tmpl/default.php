<?php
defined('_JEXEC') or die;

use Joomla\CMS\Router\Route;
use Joomla\Component\Content\Site\Helper\RouteHelper;

?>

<?php if (empty($items)) return; ?>

<div class="row g-1">

    <!-- Right: Big Item -->
    <?php
    $item = $items[0];
    $img = '';
    if (!empty($item->images)) {
        $images = json_decode($item->images);
        $img = $images->image_intro ?? '';
    }
    $link = Route::_(RouteHelper::getArticleRoute($item->id));
    $catLink = Route::_(RouteHelper::getCategoryRoute($item->catid));
    ?>
    <div class="col-lg-6">
        <div class="position-relative overflow-hidden bg-dark img-hover-blur-dark">
            <div class="ratio ratio-16x9">
                <?php if ($img): ?>
                    <img src="<?= $img ?>" class="w-100 h-100 object-fit-cover" alt="<?= htmlspecialchars($item->title) ?>" loading="lazy">
                <?php endif; ?>
            </div>
            <div class="position-absolute top-0 end-0 m-2 z-2">
                <a href="<?= $catLink ?>" class="badge text-bg-danger text-decoration-none fw-normal">
                    <span><?= htmlspecialchars(preg_replace('/^اخبار\s*/u', '', $item->cat)) ?></span>
                </a>
            </div>
            <div class="position-absolute bottom-0 start-0 p-4 text-white w-100 caption-overlay pt-22">
                <h1 class="fw-bold lh-base m-0 fs-5"><?= htmlspecialchars($item->title) ?></h1>
            </div>
            <a href="<?= $link ?>" class="stretched-link"></a>
        </div>
    </div>

    <!-- Left: Small Items Grid (4 items) -->
    <div class="col-lg-6 d-flex flex-column gap-3">
        <div class="row g-1">
            <?php foreach (array_slice($items, 1, 5) as $i => $item): ?>
                <?php
                $img = '';
                if (!empty($item->images)) {
                    $images = json_decode($item->images);
                    $img = $images->image_intro ?? '';
                }
                $link = Route::_(RouteHelper::getArticleRoute($item->id));
                $catLink = Route::_(RouteHelper::getCategoryRoute($item->catid));
                ?>
                <div class="col-lg-6 col-md-6 col-sm-12">
                    <div class="position-relative overflow-hidden bg-dark img-hover-blur-dark">
                        <div class="ratio ratio-16x9">
                            <?php if ($img): ?>
                                <img src="<?= $img ?>" class="w-100 h-100 object-fit-cover" alt="<?= htmlspecialchars($item->title) ?>" loading="lazy">
                            <?php endif; ?>
                        </div>
                        <div class="position-absolute top-0 end-0 m-2 z-2">
                            <a href="<?= $catLink ?>" class="badge text-bg-danger text-decoration-none fw-normal">
                                <span><?= htmlspecialchars(preg_replace('/^اخبار\s*/u', '', $item->cat)) ?></span>
                            </a>
                        </div>
                        <div class="position-absolute bottom-0 start-0 p-3 text-white w-100 caption-overlay pt-22">
                            <h4 class="m-0 fw-bold lh-base fs-5"><?= htmlspecialchars($item->title) ?></h4>
                        </div>
                        <a href="<?= $link ?>" class="stretched-link"></a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>


</div>
