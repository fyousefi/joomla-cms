<?php
/**
 * @package     mod_spotlight
 * @subpackage  Layout override for subform repeatable-table with row numbering
 */
defined('_JEXEC') or die;

use Joomla\CMS\Layout\FileLayout;

// Render the core repeatable-table layout but wrap it and inject CSS counters for numbering
$inner = new FileLayout('joomla.form.field.subform.repeatable-table');
?>
<style>
    /* Number rows 1..N and keep in sync after drag (pure CSS via counters) */
    .numbered-subform tbody { counter-reset: rownum; }
    .numbered-subform tbody tr { counter-increment: rownum; }
    /* Prepend the number inside the first cell (the one that already contains the drag handle) */
    .numbered-subform tbody tr > td:first-child { position: relative; padding-left: 2.2rem; }
    .numbered-subform tbody tr > td:first-child::before {
        content: counter(rownum) ".";
        position: absolute;
        left: .6rem;
        top: 50%;
        transform: translateY(-50%);
        font-weight: 600;
        opacity: .8;
    }
</style>
<div class="numbered-subform">
    <?php echo $inner->render($displayData); ?>
</div>
