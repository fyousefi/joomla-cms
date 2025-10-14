<?php
defined('_JEXEC') or die;

use Joomla\Utilities\ArrayHelper;
use Joomla\CMS\Router\Route;

/** @var array $displayData */
$module  = $displayData['module'];
$params  = $displayData['params'];

if ($module->content === null || $module->content === '') return;

$moduleTag = $params->get('module_tag', 'div');
$moduleAttribs['class'] = trim('mod-style-review mod-spotlight-wrap mb-0 ' . htmlspecialchars((string) $params->get('moduleclass_sfx',''), ENT_QUOTES, 'UTF-8'));

$headerTag   = htmlspecialchars($params->get('header_tag', 'h3'), ENT_QUOTES, 'UTF-8');
$headerClass = trim((string) $params->get('header_class', ''));
$headerAttribs['class'] = 'mod-title review-title text-white mt-2 ms-4 fs-5' . ($headerClass ? ' ' . $headerClass : '');

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

$baseId = 'mod-spotlight-' . (int) $module->id;

// Build “view all” link if configured
$linkHref = '';
$label    = trim((string) $params->get('review_link_label', ''));
$src      = (string) $params->get('review_link_source', 'menu');

if ($src === 'menu' && (int) $params->get('review_link_menu')) {
    $linkHref = Route::_('index.php?Itemid=' . (int) $params->get('review_link_menu'));
} elseif ($src === 'category' && (int) $params->get('review_link_cat')) {
    $linkHref = Route::_('index.php?option=com_content&view=category&id=' . (int) $params->get('review_link_cat'));
}
?>
<<?php echo $moduleTag; ?> <?php echo ArrayHelper::toString($moduleAttribs); ?>>

<?php if (!empty($module->showtitle)) : ?>
    <div class="mod-head py-2 pt-4">
        <div class="d-flex align-items-center flex-nowrap gap-3">
        <?php echo $headerHtml; ?>

            <!-- the line between title and button -->
            <div class="flex-grow-1 border-top border-3 order-light-subtle opacity-75"></div>

        <?php if ($linkHref && $label): ?>
            <a class="btn btn-danger btn-sm rounded-0 me-4 fs-9" href="<?php echo $linkHref; ?>">
                <?php echo htmlspecialchars($label, ENT_QUOTES, 'UTF-8'); ?>
            </a>
        <?php endif; ?>
        </div>
    </div>
<?php endif; ?>

<div class="mod-body p-3 rounded-3">
    <?php echo $module->content; ?>
</div>

<!-- Bottom-centered controls (one pair per breakpoint) -->
<div class="spotlight-controls d-flex justify-content-center gap-2 pb-3">
    <!-- Phones -->
    <button class="spotlight-control d-inline-flex d-md-none rounded-0" type="button"
            data-bs-target="#<?php echo $baseId; ?>-xs" data-bs-slide="prev" aria-label="Prev">
        <span class="fa fa-angle-right" aria-hidden="true"></span>
        <span class="visually-hidden">Prev</span>
    </button>
    <button class="spotlight-control d-inline-flex d-md-none rounded-0" type="button"
            data-bs-target="#<?php echo $baseId; ?>-xs" data-bs-slide="next" aria-label="Next">
        <span class="fa fa-angle-left" aria-hidden="true"></span>
        <span class="visually-hidden">Next</span>
    </button>

    <!-- Tablets -->
    <button class="spotlight-control d-none d-md-inline-flex d-lg-none rounded-0" type="button"
            data-bs-target="#<?php echo $baseId; ?>-md" data-bs-slide="prev" aria-label="Prev">
        <span class="fa fa-angle-right" aria-hidden="true"></span>
        <span class="visually-hidden">Prev</span>
    </button>
    <button class="spotlight-control d-none d-md-inline-flex d-lg-none rounded-0" type="button"
            data-bs-target="#<?php echo $baseId; ?>-md" data-bs-slide="next" aria-label="Next">
        <span class="fa fa-angle-left" aria-hidden="true"></span>
        <span class="visually-hidden">Next</span>
    </button>

    <!-- Desktop -->
    <button class="spotlight-control d-none d-lg-inline-flex rounded-0" type="button"
            data-bs-target="#<?php echo $baseId; ?>" data-bs-slide="prev" aria-label="Prev">
        <span class="fa fa-angle-right" aria-hidden="true"></span>
        <span class="visually-hidden">Prev</span>
    </button>
    <button class="spotlight-control d-none d-lg-inline-flex rounded-0" type="button"
            data-bs-target="#<?php echo $baseId; ?>" data-bs-slide="next" aria-label="Next">
        <span class="fa fa-angle-left" aria-hidden="true"></span>
        <span class="visually-hidden">Next</span>
    </button>
</div>

</<?php echo $moduleTag; ?>>
