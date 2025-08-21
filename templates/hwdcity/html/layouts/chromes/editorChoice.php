<?php
/**
 * @package     Joomla.Site
 * @subpackage  Templates.hwcity
 */

defined('_JEXEC') or die;

use Joomla\Utilities\ArrayHelper;
use Joomla\CMS\Language\Text;

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
$headerAttribs          = [];
$headerAttribs['class'] = 'mod-title editor-choice' . ($headerClass !== '' ? ' ' . $headerClass : '');

// ARIA
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

// Base IDs used by Slider.php (must exist there)
$baseId   = 'mod-spotlight-' . (int) $module->id;
$prevLbl  = Text::_('MOD_SPOTLIGHT_PREV');
$nextLbl  = Text::_('MOD_SPOTLIGHT_NEXT');
?>
<<?php echo $moduleTag; ?> <?php echo ArrayHelper::toString($moduleAttribs); ?>>

<?php if (!empty($module->showtitle)) : ?>
    <div class="mod-head d-flex align-items-center justify-content-between mt-3">
        <?php echo $headerHtml; ?>
    </div>
<?php endif; ?>

<div class="mod-body p-3 rounded-3">
    <?php echo $module->content; ?>
</div>

<!-- External controls: one pair per breakpoint, targeting the visible carousel -->
<!-- Phones (xs/sm) → #mod-spotlight-{id}-xs -->
<button class="spotlight-control spotlight-prev d-inline-flex d-md-none" type="button"
        data-bs-target="#<?php echo $baseId; ?>-xs" data-bs-slide="prev" aria-label="<?php echo $prevLbl; ?>">
    <span class="carousel-control-prev-icon" aria-hidden="true"></span>
    <span class="visually-hidden"><?php echo $prevLbl; ?></span>
</button>
<button class="spotlight-control spotlight-next d-inline-flex d-md-none" type="button"
        data-bs-target="#<?php echo $baseId; ?>-xs" data-bs-slide="next" aria-label="<?php echo $nextLbl; ?>">
    <span class="carousel-control-next-icon" aria-hidden="true"></span>
    <span class="visually-hidden"><?php echo $nextLbl; ?></span>
</button>

<!-- Tablets (md only) → #mod-spotlight-{id}-md -->
<button class="spotlight-control spotlight-prev d-none d-md-inline-flex d-lg-none" type="button"
        data-bs-target="#<?php echo $baseId; ?>-md" data-bs-slide="prev" aria-label="<?php echo $prevLbl; ?>">
    <span class="carousel-control-prev-icon" aria-hidden="true"></span>
    <span class="visually-hidden"><?php echo $prevLbl; ?></span>
</button>
<button class="spotlight-control spotlight-next d-none d-md-inline-flex d-lg-none" type="button"
        data-bs-target="#<?php echo $baseId; ?>-md" data-bs-slide="next" aria-label="<?php echo $nextLbl; ?>">
    <span class="carousel-control-next-icon" aria-hidden="true"></span>
    <span class="visually-hidden"><?php echo $nextLbl; ?></span>
</button>

<!-- Desktop (lg+) → #mod-spotlight-{id} -->
<button class="spotlight-control spotlight-prev d-none d-lg-inline-flex" type="button"
        data-bs-target="#<?php echo $baseId; ?>" data-bs-slide="prev" aria-label="<?php echo $prevLbl; ?>">
    <span class="carousel-control-prev-icon" aria-hidden="true"></span>
    <span class="visually-hidden"><?php echo $prevLbl; ?></span>
</button>
<button class="spotlight-control spotlight-next d-none d-lg-inline-flex" type="button"
        data-bs-target="#<?php echo $baseId; ?>" data-bs-slide="next" aria-label="<?php echo $nextLbl; ?>">
    <span class="carousel-control-next-icon" aria-hidden="true"></span>
    <span class="visually-hidden"><?php echo $nextLbl; ?></span>
</button>

</<?php echo $moduleTag; ?>>
