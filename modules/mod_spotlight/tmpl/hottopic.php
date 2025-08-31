<?php
defined('_JEXEC') or die;
/** @var array $items */
/** @var \Joomla\Registry\Registry $params */
/** @var \stdClass $module */

use Joomla\CMS\Router\Route;
use Joomla\CMS\Language\Text;

if (empty($items)) :
    echo '<div class="text-muted small py-3">' . Text::_('MOD_SPOTLIGHT_EMPTY') . '</div>';
    return;
endif;
?>
<div class="mod-spotlight-hot list-inline m-0">
    <?php foreach ($items as $item): ?>
        <?php
        $href  = Route::_('index.php?option=com_content&view=article&id=' . (int) $item->id);
        $label = trim((string) $item->title);
        ?>
        <a href="<?php echo $href; ?>"
           class="ht-link fw-normal fs-9 me-2 mb-2 px-2 py-1 text-decoration-none">
            <?php echo htmlspecialchars($label !== '' ? $label : ('#' . (int) $item->id), ENT_QUOTES, 'UTF-8'); ?>
        </a>
    <?php endforeach; ?>
</div>
