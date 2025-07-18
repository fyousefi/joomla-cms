<?php
defined('_JEXEC') or die;

$h  = $params->get('header', 'h4');
$grating = "<{$h}>{$hello}</{$h}>";
?>

<?php echo $grating; ?>
