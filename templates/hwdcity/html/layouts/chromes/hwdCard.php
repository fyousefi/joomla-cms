<?php
/**
 * @package     Joomla.Site
 * @subpackage  Templates.hwdcity
 *
 * Chrome: hwdCard (Bootstrap 5.3+, flex/column, CLS-safe wrapper)
 * Usage: set Module Style = hwdCard
 */

defined('_JEXEC') or die;

use Joomla\Utilities\ArrayHelper;

$module  = $displayData['module'];
$params  = $displayData['params'];
$attribs = $displayData['attribs'];

if ($module->content === null || $module->content === '') {
    return;
}

$moduleTag = $params->get('module_tag', 'section');
$headerTag = htmlspecialchars($params->get('header_tag', 'h3'), ENT_QUOTES, 'UTF-8');
$headerCls = trim((string) $params->get('header_class', ''));

// WRAPPER CLASSES (no .row to avoid overflow)
$wrapper = trim(
    ($module->position ?: '') . ' hwd-card d-flex flex-column bg-body'
    . ' ' . htmlspecialchars($params->get('moduleclass_sfx', ''), ENT_QUOTES, 'UTF-8')
);

// Build attributes
$moduleAttribs = ['class' => $wrapper];
$headerAttribs = ['class' => 'm-0'];

// Accessibility
if ($moduleTag !== 'div') {
    if ($module->showtitle) {
        $moduleAttribs['aria-labelledby'] = 'mod-' . $module->id;
        $headerAttribs['id'] = 'mod-' . $module->id;
    } else {
        $moduleAttribs['aria-label'] = $module->title;
    }
}

// Allow adding extra classes via chrome attribs
if (!empty($attribs['class'])) {
    $moduleAttribs['class'] .= ' ' . htmlspecialchars($attribs['class'], ENT_QUOTES, 'UTF-8');
}

// Header style (centered, red like screenshots)
$headerWrapCls = 'hwd-card-header d-flex justify-content-start align-items-center py-26 px-3 border-bottom border-2';
$headerTextCls = 'text-danger fw-bold fs-7 m-0 ' . $headerCls;

// Render
?>
<<?php echo $moduleTag; ?> <?php echo ArrayHelper::toString($moduleAttribs); ?>>

<?php if ($module->showtitle) : ?>
<div class="<?php echo $headerWrapCls; ?>">
    <<?php echo $headerTag; ?> <?php echo ArrayHelper::toString(['class' => $headerTextCls]); ?>>
    <?php echo $module->title; ?>
    </<?php echo $headerTag; ?>>
    </div>
<?php endif; ?>

<div class="hwd-card-body">
    <?php echo $module->content; ?>
</div>

</<?php echo $moduleTag; ?>>
