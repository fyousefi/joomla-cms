<?php
/**
 * @package     Joomla.Site
 * @subpackage  Templates.hwcity
 */

defined('_JEXEC') or die;

use Joomla\Utilities\ArrayHelper;

/** @var array $displayData */
$module  = $displayData['module'];
$params  = $displayData['params'];
$attribs = $displayData['attribs'];

if ($module->content === null || $module->content === '') {
    return;
}

$moduleTag     = $params->get('module_tag', 'div');
$moduleAttribs = [];
$headerTag     = htmlspecialchars($params->get('header_tag', 'h3'), ENT_QUOTES, 'UTF-8');
$headerClass   = trim((string) $params->get('header_class', ''));

// Wrapper classes (simple, no slider classes)
$moduleAttribs['class'] = trim(
    'mod-style-hottopic bg-white d-none d-sm-block' .
    htmlspecialchars((string) $params->get('moduleclass_sfx', ''), ENT_QUOTES, 'UTF-8')
);

// Header classes (allow admin “Header Class” too)
$headerAttribs          = [];
$headerAttribs['class'] = 'mod-title hot-topic-title text-danger' . ($headerClass !== '' ? ' ' . $headerClass : '');

// ARIA
if ($moduleTag !== 'div') {
    if (!empty($module->showtitle)) {
        $moduleAttribs['aria-labelledby'] = 'mod-' . (int) $module->id;
        $headerAttribs['id']              = 'mod-' . (int) $module->id;
    } else {
        $moduleAttribs['aria-label'] = htmlspecialchars($module->title ?? '', ENT_QUOTES, 'UTF-8');
    }
}

// Title HTML (icon + text). Font Awesome hashtag at the start.
$headerHtml = '<' . $headerTag . ' ' . ArrayHelper::toString($headerAttribs) . '>' .
    '<span class="hot-topic-icon d-inline-flex align-items-center justify-content-center rounded-circle border border-danger border-2 text-danger me-2">' .
    '<i class="fa-solid fa-hashtag" aria-hidden="true"></i>' .
    '<span class="visually-hidden">#</span>' .
    '</span>' .
    htmlspecialchars($module->title ?? '', ENT_QUOTES, 'UTF-8') .
    '</' . $headerTag . '>';
?>
<<?php echo $moduleTag; ?> <?php echo ArrayHelper::toString($moduleAttribs); ?>>

<?php if (!empty($module->showtitle)) : ?>
    <div class="mod-head d-flex align-items-center ms-3">
        <?php echo $headerHtml; ?>
<?php endif; ?>
        <div class="mod-body p-3 rounded-3">
            <?php echo $module->content; ?>
        </div>
<?php if (!empty($module->showtitle)) : ?>
    </div>
<?php endif; ?>


</<?php echo $moduleTag; ?>>
