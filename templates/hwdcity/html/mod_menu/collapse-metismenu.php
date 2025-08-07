<?php

/**
 * @package     Joomla.Site
 * @subpackage  mod_menu
 *
 * @copyright   (C) 2025 AsiaSun.ir, Pvt. Ltd. <https://www.asiasun.ir>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

HTMLHelper::_('bootstrap.collapse');
?>

<nav class="navbar navbar-expand-lg pt-md-2 pt-lg-0" aria-label="<?php echo htmlspecialchars($module->title, ENT_QUOTES, 'UTF-8'); ?>">
    <button class="navbar-toggler navbar-toggler-right" type="button" data-bs-toggle="collapse" data-bs-target="#navbar<?php echo $module->id; ?>" aria-controls="navbar<?php echo $module->id; ?>" aria-expanded="false" aria-label="<?php echo Text::_('MOD_MENU_TOGGLE'); ?>">
        <span class="icon-menu" aria-hidden="true"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbar<?php echo $module->id; ?>">
        <?php require __DIR__ . '/dropdown-metismenu.php'; ?>
    </div>
</nav>
