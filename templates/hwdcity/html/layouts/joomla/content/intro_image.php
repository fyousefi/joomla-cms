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
$pageClass = (string) ($params['pageclass_sfx'] ?? '');
$isFullLayout = (bool) preg_match('/(?:^|\s)full-width(?:\s|$)/', $pageClass);

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
$heightClass  = $isFullLayout ? 'h-60' : 'h-65';

// Category badge font-size
$badgeLblFs  = $isFullLayout ? 'fs-6' : 'fs-13';
?>
<figure class="<?php echo $this->escape($imgclass); ?> item-image position-relative mb-3 <?php echo $heightClass; ?>">
    <?php if ($params->get('link_intro_image') && ($params->get('access-view') || $params->get('show_noauth', '0') == '1')) : ?>
        <a href="<?php echo Route::_(RouteHelper::getArticleRoute($displayData->slug, $displayData->catid, $displayData->language)); ?>" title="<?php echo $this->escape($displayData->title); ?>">
            <span class="triangle-up position-absolute bottom-0 mx-5"></span>
            <?php echo LayoutHelper::render('joomla.html.image', $layoutAttr); ?>
        </a>
    <?php else : ?>
        <?php echo LayoutHelper::render('joomla.html.image', $layoutAttr); ?>
    <?php endif; ?>

    <?php if ($badgeLbl !== null) : ?>
        <div class="position-absolute top-0 end-0 m-3 z-2">
            <a href="<?php echo $catLink; ?>" class="badge text-bg-danger text-decoration-none fw-normal <?php echo $badgeLblFs; ?>">
                <span><?php echo $badgeLbl; ?></span>
            </a>
        </div>
    <?php endif; ?>
    <?php if ($score !== null): ?>
        <div class="score-circle score-circle-sm position-absolute mt-3 ms-3 top-0 start-0 " style="--score-percent: <?php echo htmlspecialchars(number_format($percent, 2, '.', ''), ENT_QUOTES, 'UTF-8'); ?>%">
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
