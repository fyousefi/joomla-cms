<?php

/**
 * @package     Joomla.Site
 * @subpackage  Layout
 *
 * @copyright   (C) 2013 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\Router\Route;
use Joomla\Component\Content\Site\Helper\RouteHelper;

$params  = $displayData->params;
$images  = json_decode($displayData->images);

if (empty($images->image_intro)) {
    return;
}

$imgclass   = empty($images->float_intro) ? $params->get('float_intro') : $images->float_intro;
$layoutAttr = [
    'src' => $images->image_intro,
    'alt' => empty($images->image_intro_alt) && empty($images->image_intro_alt_empty) ? false : $images->image_intro_alt,
];

// Category badge data
$catLink  = !empty($displayData->catid) ? Route::_(RouteHelper::getCategoryRoute($displayData->catid)) : '#';
$catTitle = isset($displayData->category_title) ? (string) $displayData->category_title : '';
$removePrefix = false; // ← set to false if you don't want to strip "اخبار"
$badgeLbl = htmlspecialchars($removePrefix ? preg_replace('/^اخبار\s*/u', '', $catTitle) : $catTitle, ENT_QUOTES, 'UTF-8');

// Recognize the page
$isFullLayout = (bool) $params->get('fullwidth', 0);

// --- compute $total_score safely ---
$field_count = count($displayData->jcfields);

// init
$total_score = null;
$field_i     = 0;

// get n-1 field value
foreach ($displayData->jcfields as $key => $value) {
    $field_i++;
    if ($field_i === $field_count - 1) {
        $raw_value   = $displayData->jcfields[$key]->value ?? '';
        // keep only digits and dot, then cast
        $numeric_val = (float) preg_replace('/[^\d.]+/', '', (string) $raw_value);
        $total_score = $numeric_val;
        break;
    }
}

// compute score (0–10 scale) or null if not applicable
$score = ($isFullLayout && $total_score !== null && $total_score > 10)
    ? ($total_score / 10.0)
    : null;

// Precompute numeric percent for CSS var
$percent = ($score !== null) ? ($score * 10.0) : null;

// Figure height
$heightClass  = $isFullLayout ? 'h-md-40 h-lg-60 zoom-container zoom-dark overflow-hidden' : 'h-65';

// Category badge font-size
$badgeLblFs  = $isFullLayout ? 'fs-6' : 'fs-13';

// When full-width, drop the default bottom margin on the <figure>
$figureMargin = $isFullLayout ? 'mb-0' : 'mb-3';

// $layoutAttr
if ($isFullLayout) {
    // Make full-width article image fill and center inside its figure
    $layoutAttr['class'] = trim(($layoutAttr['class'] ?? '') . ' w-100 h-100 object-fit-cover');
    // Bootstrap lacks object-position utility → inline style for center
    $layoutAttr['style'] = trim(($layoutAttr['style'] ?? '') . ' object-position:center;');
}

?>
<figure class="<?php echo $this->escape($imgclass); ?> item-image position-relative <?php echo $figureMargin; ?> <?php echo $heightClass; ?>">
    <?php if ($params->get('link_intro_image') && ($params->get('access-view') || $params->get('show_noauth', '0') == '1')) : ?>
            <?php echo !$isFullLayout ? '<span class="triangle-up position-absolute bottom-0 mx-5"></span>' : '';?>
            <?php echo LayoutHelper::render('joomla.html.image', $layoutAttr); ?>
    <?php else : ?>
        <?php echo LayoutHelper::render('joomla.html.image', $layoutAttr); ?>
    <?php endif; ?>

    <?php if ($isFullLayout): ?>
        <div class="position-absolute top-0 start-0 w-100 h-100 d-flex align-items-center z-2" style="pointer-events:none;">
            <div class="w-100 p-3 p-md-4 mt-5" style="pointer-events:auto;">
                <div class="d-flex justify-content-center"></div>

                <?php if ($params->get('show_title')): ?>
                    <h1 class="lh-base fw-bold fs-16 text-center page-start" style="pointer-events:auto;">
                        <a class="link-light text-decoration-none d-flex justify-content-center text-center py-25 link-title fs-lg-16 fs-md-5 fs-15"
                           href="<?php echo Route::_(RouteHelper::getArticleRoute($displayData->slug, $displayData->catid, $displayData->language)); ?>">
                            <?php echo $this->escape($displayData->title); ?>
                        </a>
                    </h1>
                <?php endif; ?>

                <?php
                // Info block "above" (meta + social share), now styled white in your info_block when fullwidth=1
                $assocParam  = (Joomla\CMS\Language\Associations::isEnabled() && $params->get('show_associations'));
                $useDefList  = $params->get('show_modify_date') || $params->get('show_publish_date') || $params->get('show_create_date')
                    || $params->get('show_hits') || $params->get('show_category') || $params->get('show_parent_category')
                    || $params->get('show_author') || $assocParam;
                $infoPos     = (int) $params->get('info_block_position', 0);

                if ($useDefList && ($infoPos === 0 || $infoPos === 2)) {
                    echo LayoutHelper::render('joomla.content.info_block', ['item' => $displayData, 'params' => $params, 'position' => 'above']);
                }
                ?>
            </div>
        </div>
    <?php endif; ?>

    <?php if ($badgeLbl !== '') : ?>
        <div class="position-absolute top-0 end-0 m-3 z-2">
            <a href="<?php echo $catLink; ?>" class="badge text-bg-danger text-decoration-none fw-normal <?php echo $badgeLblFs; ?>">
                <span><?php echo $badgeLbl; ?></span>
            </a>
        </div>
    <?php endif; ?>

    <?php if ($score !== null): ?>
        <div class="score-circle score-circle-sm position-absolute mt-3 ms-3 top-0 start-0 z-2 " style="--score-percent: <?php echo htmlspecialchars(number_format($percent, 2, '.', ''), ENT_QUOTES, 'UTF-8'); ?>%">
            <div class="score-bg"></div>
            <div class="score-progress"></div>
            <div class="score-center">
                <div class="score-value ss02">
                    <?php echo htmlspecialchars(number_format($score, 1, '.', ''), ENT_QUOTES, 'UTF-8'); ?>
                </div>
            </div>
        </div>
    <?php endif; ?>
    <?php if (isset($images->image_intro_caption) && $images->image_intro_caption !== '') : ?>
        <figcaption class="caption"><?php echo $this->escape($images->image_intro_caption); ?></figcaption>
    <?php endif; ?>
</figure>
