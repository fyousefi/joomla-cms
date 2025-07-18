<?php
defined('_JEXEC') or die;

$document = $this->app->getDocument();
$wa = $document->getWebAssetManager();
$wa->getRegistry()->addExtensionRegistryFile('mod_showcase');
$wa->useScript('mod_showcase.add-suffix');
$wa->useStyle('mod_showcase.example');

// Pass the suffix to add down to js
$document->addScriptOptions('mod_showcase.vars', ['suffix' => '!']);

$h  = $params->get('header', 'h4');
$greeting = "<{$h} class='mod_showcase'>{$hello}</{$h}>"
?>

<?php echo $greeting; ?>
