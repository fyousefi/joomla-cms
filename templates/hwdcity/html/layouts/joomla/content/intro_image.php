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
?>
<figure class="<?php echo $this->escape($imgclass); ?> item-image position-relative mb-3 h-65">
    <?php if ($params->get('link_intro_image') && ($params->get('access-view') || $params->get('show_noauth', '0') == '1')) : ?>
        <a href="<?php echo Route::_(RouteHelper::getArticleRoute($displayData->slug, $displayData->catid, $displayData->language)); ?>" title="<?php echo $this->escape($displayData->title); ?>">
            <span class="triangle-up position-absolute bottom-0 mx-5"></span>
            <?php echo LayoutHelper::render('joomla.html.image', $layoutAttr); ?>
        </a>
    <?php else : ?>
        <?php echo LayoutHelper::render('joomla.html.image', $layoutAttr); ?>
    <?php endif; ?>

    <?php if ($badgeLbl !== '') : ?>
        <div class="position-absolute top-0 end-0 m-3 z-2">
            <a href="<?php echo $catLink; ?>" class="badge text-bg-danger text-decoration-none fw-normal fs-13">
                <span><?php echo $badgeLbl; ?></span>
            </a>
        </div>
    <?php endif; ?>

    <?php if (isset($images->image_intro_caption) && $images->image_intro_caption !== '') : ?>
        <figcaption class="caption"><?php echo $this->escape($images->image_intro_caption); ?></figcaption>
    <?php endif; ?>
</figure>
