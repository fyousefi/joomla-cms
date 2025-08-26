<?php
defined('_JEXEC') or die;

use Joomla\Utilities\ArrayHelper;
use Joomla\CMS\Router\Route;

/** @var array $displayData */
$module  = $displayData['module'];
$params  = $displayData['params'];

if ($module->content === null || $module->content === '') return;

$moduleTag = $params->get('module_tag', 'div');
$moduleAttribs['class'] = trim('mod-style-media ' . htmlspecialchars((string) $params->get('moduleclass_sfx',''), ENT_QUOTES, 'UTF-8'));

$headerTag   = htmlspecialchars($params->get('header_tag', 'h3'), ENT_QUOTES, 'UTF-8');
$headerClass = trim((string) $params->get('header_class', ''));
$headerAttribs['class'] = 'mod-title media-title ' . ($headerClass ?: '');

if ($moduleTag !== 'div') {
    if (!empty($module->showtitle)) {
        $moduleAttribs['aria-labelledby'] = 'mod-' . (int) $module->id;
        $headerAttribs['id']              = 'mod-' . (int) $module->id;
    } else {
        $moduleAttribs['aria-label'] = htmlspecialchars($module->title ?? '', ENT_QUOTES, 'UTF-8');
    }
}

$headerHtml = '<' . $headerTag . ' ' . ArrayHelper::toString($headerAttribs) . '>' .
    htmlspecialchars($module->title ?? '', ENT_QUOTES, 'UTF-8') .
    '</' . $headerTag . '>';

$linkHref = '';
$label    = trim((string) $params->get('media_link_label', ''));
$src      = (string) $params->get('media_link_source', 'menu');

if ($src === 'menu' && (int) $params->get('media_link_menu')) {
    $linkHref = Route::_('index.php?Itemid=' . (int) $params->get('media_link_menu'));
} elseif ($src === 'category' && (int) $params->get('media_link_cat')) {
    $linkHref = Route::_('index.php?option=com_content&view=category&id=' . (int) $params->get('media_link_cat'));
}
?>
<<?php echo $moduleTag; ?> <?php echo ArrayHelper::toString($moduleAttribs); ?>>

<?php if (!empty($module->showtitle)) : ?>
    <header class="d-flex align-items-center gap-3 mb-3">
        <?php if ($label && $linkHref): ?>
            <a class="btn btn-danger btn-sm rounded-1 px-3 order-2 order-lg-1" href="<?php echo $linkHref; ?>">
                <?php echo htmlspecialchars($label, ENT_QUOTES, 'UTF-8'); ?>
            </a>
        <?php endif; ?>

        <div class="flex-grow-1 border-top opacity-50 order-1 order-lg-2"></div>

        <div class="order-0 order-lg-3">
            <?php echo $headerHtml; ?>
        </div>
    </header>
<?php endif; ?>

<div class="mod-body">
    <?php echo $module->content; ?>
</div>

</<?php echo $moduleTag; ?>>
