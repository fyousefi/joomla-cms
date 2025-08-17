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

// Wrapper classes
$moduleAttribs['class'] = trim(
    ($module->position ?? '') . ' no-card mod-style-editorchoice mod-spotlight-wrap ' .
    htmlspecialchars((string) $params->get('moduleclass_sfx', ''), ENT_QUOTES, 'UTF-8')
);

// Header classes (default + optional user classes)
$headerAttribs = [];
$headerAttribs['class'] = 'mod-title editor-choice' . ($headerClass !== '' ? ' ' . $headerClass : '');

// ARIA
if ($moduleTag !== 'div') {
    if ($module->showtitle) {
        $moduleAttribs['aria-labelledby'] = 'mod-' . (int) $module->id;
        $headerAttribs['id'] = 'mod-' . (int) $module->id;
    } else {
        $moduleAttribs['aria-label'] = htmlspecialchars($module->title ?? '', ENT_QUOTES, 'UTF-8');
    }
}

$headerHtml = '<' . $headerTag . ' ' . ArrayHelper::toString($headerAttribs) . '>' .
    htmlspecialchars($module->title ?? '', ENT_QUOTES, 'UTF-8') .
    '</' . $headerTag . '>';

// Target the Spotlight carousel ID used by the module layout
$carouselId = 'mod-spotlight-' . (int) $module->id;
?>
<<?php echo $moduleTag; ?> <?php echo ArrayHelper::toString($moduleAttribs); ?>>
<?php if (!empty($module->showtitle)) : ?>
    <header class="mod-head d-flex align-items-center justify-content-between mt-3">
        <?php echo $headerHtml; ?>
    </header>
<?php endif; ?>

<div class="mod-body p-3 rounded-3">
    <?php echo $module->content; ?>
</div>

<!-- External controls (outside the module content), pure Bootstrap data-API -->
<button class="spotlight-control spotlight-prev" type="button"
        data-bs-target="#<?php echo $carouselId; ?>" data-bs-slide="prev" aria-label="Prev">
    <span class="carousel-control-prev-icon" aria-hidden="true"></span>
    <span class="visually-hidden">Prev</span>
</button>

<button class="spotlight-control spotlight-next" type="button"
        data-bs-target="#<?php echo $carouselId; ?>" data-bs-slide="next" aria-label="Next">
    <span class="carousel-control-next-icon" aria-hidden="true"></span>
    <span class="visually-hidden">Next</span>
</button>
</<?php echo $moduleTag; ?>>
