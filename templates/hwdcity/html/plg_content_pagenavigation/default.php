<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  Content.pagenavigation
 *
 * @copyright   (C) 2013 Open Source Matters
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;

/** @var \Joomla\Plugin\Content\PageNavigation\Extension\PageNavigation $this */
$this->loadLanguage();
$lang = $this->getLanguage();
?>

<nav class="pagenavigation" aria-label="<?php echo Text::_('PLG_PAGENAVIGATION_ARIA_LABEL'); ?>">

    <?php if (!empty($row->prev)) : ?>
        <?php $prevChevron = $lang->isRtl() ? 'right' : 'left'; ?>
        <a class="bg-dark text-white py-2 px-3 my-2 d-flex align-items-center justify-content-between text-decoration-none"
           href="<?php echo Route::_($row->prev); ?>" rel="prev">
            <span class="visually-hidden">
                <?php echo Text::sprintf('JPREVIOUS_TITLE', htmlspecialchars($rows[$location - 1]->title)); ?>
            </span>

            <!-- Left chevron -->
            <span class="d-inline-flex align-items-center">
                <span class="icon-chevron-<?php echo $prevChevron; ?>" aria-hidden="true"></span>
            </span>

            <!-- Center label -->
            <span class="fw-normal fs-8 text-center flex-grow-1">
                <?php echo $row->prev_label; ?>
            </span>

            <!-- Right placeholder to keep label centered -->
            <span class="opacity-0">·</span>
        </a>
    <?php endif; ?>

    <?php if (!empty($row->next)) : ?>
        <?php $nextChevron = $lang->isRtl() ? 'left' : 'right'; ?>
        <a class="bg-dark text-white py-2 px-3 my-2 d-flex align-items-center justify-content-between text-decoration-none"
           href="<?php echo Route::_($row->next); ?>" rel="next">
            <span class="visually-hidden">
                <?php echo Text::sprintf('JNEXT_TITLE', htmlspecialchars($rows[$location + 1]->title)); ?>
            </span>

            <!-- Left placeholder to keep label centered -->
            <span class="opacity-0">·</span>

            <!-- Center label -->
            <span class="fw-normal fs-8 text-center flex-grow-1">
                <?php echo $row->next_label; ?>
            </span>

            <!-- Right chevron -->
            <span class="d-inline-flex align-items-center">
                <span class="icon-chevron-<?php echo $nextChevron; ?>" aria-hidden="true"></span>
            </span>
        </a>
    <?php endif; ?>

</nav>
