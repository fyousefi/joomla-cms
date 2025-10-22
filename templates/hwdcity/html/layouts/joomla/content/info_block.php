<?php

/**
 * @package     Joomla.Site
 * @subpackage  Layout
 *
 * @copyright   (C) 2017 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\Component\Content\Site\Helper\RouteHelper;
use Joomla\CMS\Factory;

$blockPosition = $displayData['params']->get('info_block_position', 0);
$input = Factory::getApplication()->getInput();
$option = $input->getCmd('option', '');
$view   = $input->getCmd('view', '');
$articleView = ($option === 'com_content' && $view === 'article');

// Get the current URL and article title
$currentUrl = Route::_(RouteHelper::getArticleRoute($displayData['item']->slug, $displayData['item']->catid, $displayData['item']->language));
$articleTitle = $displayData['item']->title;

// Use our runtime flag passed via params
$isFullLayout = (bool) ($displayData['params']->get('fullwidth', 0));

// When full-width: force "above", center, and make text white
if ($isFullLayout) {
    $infoClass = 'article-info fs-14 text-center text-white ' . ($articleView ? 'py-3' : 'py-2');
} else {
    $infoClass = 'article-info text-secondary fs-14 ' . ($articleView ? 'py-3' : 'py-2');
}
?>
<div class="<?= $infoClass ?>">

    <?php
    if (
        $displayData['position'] === 'above' && ($blockPosition == 0 || $blockPosition == 2)
        || $displayData['position'] === 'below' && ($blockPosition == 1)
    ) : ?>
        <div class="article-info-term">
            <?php if (!$displayData['params']->get('info_block_show_title', 1)) : ?>
                <?php echo '<span class="visually-hidden">'; ?>
            <?php endif; ?>
            <?php echo Text::_('COM_CONTENT_ARTICLE_INFO'); ?>
            <?php if (!$displayData['params']->get('info_block_show_title', 1)) : ?>
                <?php echo '</span>'; ?>
            <?php endif; ?>
        </div>

        <?php if ($displayData['params']->get('show_author') && !empty($displayData['item']->author)) : ?>
            <?php echo $this->sublayout('author', $displayData); ?>
        <?php endif; ?>

        <?php if ($displayData['params']->get('show_parent_category') && !empty($displayData['item']->parent_id)) : ?>
            <?php echo $this->sublayout('parent_category', $displayData); ?>
        <?php endif; ?>

        <?php if ($displayData['params']->get('show_category')) : ?>
            <?php echo $this->sublayout('category', $displayData); ?>
        <?php endif; ?>

        <?php if ($displayData['params']->get('show_associations')) : ?>
            <?php echo $this->sublayout('associations', $displayData); ?>
        <?php endif; ?>

        <?php if ($displayData['params']->get('show_publish_date')) : ?>
            <?php echo $this->sublayout('publish_date', $displayData); ?>
        <?php endif; ?>

    <?php endif; ?>

    <?php
    if (
        $displayData['position'] === 'above' && ($blockPosition == 0)
        || $displayData['position'] === 'below' && ($blockPosition == 1 || $blockPosition == 2)
    ) : ?>
        <?php if ($displayData['params']->get('show_create_date')) : ?>
            <?php echo $this->sublayout('create_date', $displayData); ?>
        <?php endif; ?>

        <?php if ($displayData['params']->get('show_modify_date')) : ?>
            <?php echo $this->sublayout('modify_date', $displayData); ?>
        <?php endif; ?>

        <?php if ($displayData['params']->get('show_hits')) : ?>
            <?php echo $this->sublayout('hits', $displayData); ?>
        <?php endif; ?>
    <?php endif; ?>

    <?php if ($articleView) : ?>
        <!-- Social media sharing block -->
                <span class="social-icons float-end ps-1">
                    <a href="https://telegram.me/share/url?url=<?php echo $currentUrl; ?>&text=<?php echo $articleTitle; ?>"
                       target="_blank"
                       aria-label="اشتراک در تلگرام">
                        <span class="fab fa-telegram" aria-hidden="true"></span>
                    </a>
                </span>
                <span class="social-icons float-end ps-1">
                    <a href="https://x.com/intent/post?text=<?php echo $articleTitle; ?>. <?php echo $currentUrl; ?>"
                       target="_blank"
                       aria-label="اشتراک در توییتر">
                        <span class="fab fa-x-twitter" aria-hidden="true"></span>
                    </a>
                </span>
                <span class="social-icons float-end ps-1">
                    <a href="whatsapp://send?text=<?php echo $articleTitle; ?>. <?php echo $currentUrl; ?>"
                       target="_blank"
                       aria-label="اشتراک در این واتس اپ">
                        <span class="fab fa-whatsapp" aria-hidden="true"></span>
                    </a>
                </span>
                <span class="social-icons float-end">
                    <a href="mailto:?subject=<?php echo $articleTitle; ?>&body=<?php echo $currentUrl; ?>"
                       aria-label="اشتراک با ایمیل">
                        <span class="fa fa-envelope" aria-hidden="true"></span>
                    </a>
                </span>
    <?php endif; ?>
</div>

