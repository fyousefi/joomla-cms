<?php
/**
 * @package     Joomla.Site
 * @subpackage  mod_articles
 *
 * @copyright   (C) 2024 Open Source Matters, Inc.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\LayoutHelper;

/* --------------------------------------------------------------------
   Normalize layout param:
   Horizontal  → 3 columns (image above title, p-1)
   Vertical    → 1 column (image beside title, py-2)
-------------------------------------------------------------------- */
$rawLayout    = $params->get('articles_layout', 0);
$isHorizontal = (string)$rawLayout === '1' || $rawLayout === 1 || strtolower((string)$rawLayout) === 'horizontal';

/* Build UL classes (Bootstrap only; no custom CSS) */
$ulClasses = [
    'mod-articles-items',
    'mod-list',
    'px-2',
    'list-unstyled',
    'row',
    $isHorizontal ? 'row-cols-3' : 'row-cols-1',
    'g-2'
];

/* Per-item layout classes */
$anchorClass = $isHorizontal
    ? 'd-flex flex-column gap-2 text-decoration-none stretched-link'   // stack: image then title
    : 'd-flex align-items-center gap-3 text-decoration-none stretched-link'; // row: image + text

$thumbWrapClass = $isHorizontal
    ? 'w-100 ratio ratio-16x9 overflow-hidden'
    : 'flex-shrink-0 w-35 ratio ratio-16x9 overflow-hidden';

// Article padding
$articlePadding = $isHorizontal ? 'p-1' : 'py-2';
?>

<ul class="<?php echo implode(' ', $ulClasses); ?>">
    <?php foreach ($items as $item) : ?>
        <?php
        $displayInfo = $item->displayHits || $item->displayAuthorName || $item->displayCategoryTitle || $item->displayDate;

        $hasImage = in_array($params->get('img_intro_full'), ['intro', 'full'], true) && !empty($item->imageSrc);
        $imgW     = isset($item->imageWidth)  ? (int) $item->imageWidth  : null;
        $imgH     = isset($item->imageHeight) ? (int) $item->imageHeight : null;

        $link  = htmlspecialchars($item->link, ENT_COMPAT, 'UTF-8', false);
        $title = htmlspecialchars($item->title, ENT_COMPAT, 'UTF-8', false);
        $alt   = htmlspecialchars($item->imageAlt ?? $item->title ?? '', ENT_COMPAT, 'UTF-8');
        ?>
        <li class="col">
            <?php if ($displayInfo) : ?>
                <div class="mt-1 small text-body-secondary d-flex flex-wrap gap-2 fs-12">
                    <?php if ($item->displayDate) : ?><span><?php echo $item->displayDate; ?></span><?php endif; ?>
                    <?php if ($item->displayCategoryTitle) : ?>
                        <span>
                            <?php echo $item->displayCategoryLink
                                ? '<a class="link-danger text-decoration-none" href="' . $item->displayCategoryLink . '">' . $item->displayCategoryTitle . '</a>'
                                : $item->displayCategoryTitle; ?>
                        </span>
                    <?php endif; ?>
                    <?php if ($item->displayAuthorName) : ?><span><?php echo $item->displayAuthorName; ?></span><?php endif; ?>
                    <?php if ($item->displayHits) : ?><span><?php echo $item->displayHits; ?></span><?php endif; ?>
                </div>
            <?php endif; ?>

            <article class="mod-articles-item d-flex gap-3 <?php echo $articlePadding; ?> position-relative" itemscope itemtype="https://schema.org/Article">
                <a href="<?php echo $link; ?>" class="<?php echo $anchorClass; ?>">

                    <?php if ($isHorizontal && $hasImage): ?>
                        <!-- Image first in horizontal -->
                        <span class="<?php echo $thumbWrapClass; ?>">
                            <img
                                src="<?php echo htmlspecialchars($item->imageSrc, ENT_QUOTES, 'UTF-8'); ?>"
                                alt="<?php echo $alt; ?>"
                                <?php if ($imgW) : ?>width="<?php echo $imgW; ?>"<?php endif; ?>
                                <?php if ($imgH) : ?>height="<?php echo $imgH; ?>"<?php endif; ?>
                                loading="lazy" decoding="async"
                                class="position-absolute top-0 start-0 w-100 h-100 object-fit-cover">
                        </span>
                    <?php endif; ?>

                    <!-- Text (title + optional intro) -->
                    <div class="flex-grow-1">
                        <?php if ($params->get('item_title')) : ?>
                        <?php $item_heading = $params->get('item_heading', 'h4'); ?>
                        <<?php echo $item_heading; ?> class="mod-articles-title m-0 fw-semibold fs-13">
                        <?php echo $title; ?>
                    </<?php echo $item_heading; ?>>
                <?php endif; ?>

                    <?php if ($params->get('show_introtext', 0)) : ?>
                        <div class="mt-1 text-body-secondary"><?php echo $item->displayIntrotext; ?></div>
                    <?php endif; ?>
                    </div>

                    <?php if (!$isHorizontal && $hasImage): ?>
                        <!-- Image last in vertical -->
                        <span class="<?php echo $thumbWrapClass; ?>">
                            <img
                                src="<?php echo htmlspecialchars($item->imageSrc, ENT_QUOTES, 'UTF-8'); ?>"
                                alt="<?php echo $alt; ?>"
                                <?php if ($imgW) : ?>width="<?php echo $imgW; ?>"<?php endif; ?>
                                <?php if ($imgH) : ?>height="<?php echo $imgH; ?>"<?php endif; ?>
                                loading="lazy" decoding="async"
                                class="position-absolute top-0 start-0 w-100 h-100 object-fit-cover">
                        </span>
                    <?php endif; ?>

                </a>
            </article>
        </li>
    <?php endforeach; ?>
</ul>
