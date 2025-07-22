<?php
defined('_JEXEC') or die;

use Joomla\CMS\Router\Route;
use Joomla\Component\Content\Site\Helper\RouteHelper;
?>

<?php if (empty($items)) return; ?>

<div class="container">
    <div class="row g-3">

        <!-- Right: Big Item -->
        <?php
        $item = $items[0];
        $img = '';
        if (!empty($item->images)) {
            $images = json_decode($item->images);
            $img = $images->image_intro ?? '';
        }
        $link = Route::_(RouteHelper::getArticleRoute($item->id));
        ?>
        <div class="col-lg-6">
            <div class="position-relative overflow-hidden rounded">
                <div class="ratio ratio-16x9">
                    <?php if ($img): ?>
                        <img src="<?= $img ?>" class="w-100 h-100 object-fit-cover" alt="<?= htmlspecialchars($item->title) ?>" loading="lazy">
                    <?php endif; ?>
                </div>
                <div class="position-absolute top-0 end-0 m-2">
                    <span class="badge bg-danger"><?= htmlspecialchars(preg_replace('/^اخبار\s*/u', '', $item->cat)) ?></span>
                </div>
                <div class="position-absolute bottom-0 start-0 p-3 text-white bg-dark bg-opacity-50 w-100">
                    <h5 class="fw-bold lh-base m-0"><?= htmlspecialchars($item->title) ?></h5>
                </div>
                <a href="<?= $link ?>" class="stretched-link"></a>
            </div>
        </div>

        <!-- Left: Small Items Grid (4 items) -->
        <div class="col-lg-6 d-flex flex-column gap-3">
            <div class="row g-3">
                <?php foreach (array_slice($items, 1, 5) as $i => $item): ?>
                    <?php
                    $img = '';
                    if (!empty($item->images)) {
                        $images = json_decode($item->images);
                        $img = $images->image_intro ?? '';
                    }
                    $link = Route::_(RouteHelper::getArticleRoute($item->id));
                    ?>
                    <div class="col-6">
                        <div class="position-relative overflow-hidden rounded">
                            <div class="ratio ratio-16x9">
                                <?php if ($img): ?>
                                    <img src="<?= $img ?>" class="w-100 h-100 object-fit-cover" alt="<?= htmlspecialchars($item->title) ?>" loading="lazy">
                                <?php endif; ?>
                            </div>
                            <div class="position-absolute top-0 end-0 m-2">
                                <span class="badge bg-danger"><?= htmlspecialchars(preg_replace('/^اخبار\s*/u', '', $item->cat)) ?></span>
                            </div>
                            <div class="position-absolute bottom-0 start-0 p-2 text-white bg-dark bg-opacity-50 w-100">
                                <h6 class="m-0 fw-bold lh-sm"><?= htmlspecialchars($item->title) ?></h6>
                            </div>
                            <a href="<?= $link ?>" class="stretched-link"></a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>


    </div>
</div>
