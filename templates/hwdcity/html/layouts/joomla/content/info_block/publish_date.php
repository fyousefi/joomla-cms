<?php

/**
 * @package     Joomla.Site
 * @subpackage  Layout
 *
 * @copyright   (C) 2013 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

?>
<span class="published">
    <i class="icon-calendar icon-fw text-danger" aria-hidden="true"></i>
    <time datetime="<?php echo HTMLHelper::_('date', $displayData['item']->publish_up, 'c'); ?>">
        <span class="pe-lg-3 pe-md-3 pe-2"><?php echo Text::sprintf(HTMLHelper::_('date', $displayData['item']->publish_up, Text::_('DATE_FORMAT_LC3'))); ?></span>
        <i class="fa-regular fa-clock text-danger" aria-hidden="true"></i>
        <span class="pe-lg-3 pe-md-3 pe-2"><?php echo Text::sprintf(HTMLHelper::_('date', $displayData['item']->publish_up, Text::_('TPL_HWDCITY_DATE_FORMAT_TIME_ONLY'))); ?></span>
    </time>
</span>
