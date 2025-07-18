<?php
defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;

$document = $this->app->getDocument();
$wa = $document->getWebAssetManager();
$wa->getRegistry()->addExtensionRegistryFile('mod_showcase');
$wa->useScript('mod_showcase.add-suffix');
$wa->useStyle('mod_showcase.example');

// Pass the suffix to add down to js
$document->addScriptOptions('mod_showcase.vars', ['suffix' => '!']);

$h  = $params->get('header', 'h4');
$greeting = "<{$h} class='mod_showcase'>{$hello}</{$h}>";

Text::script('MOD_SHOWCASE_AJAX_OK');
Text::script('JLIB_JS_AJAX_ERROR_OTHER');
?>

<?php echo $greeting; ?>
<div>
    <p><?php echo Text::_('MOD_SHOWCASE_NUSERS'); ?><span class="mod_showcase_nusers"></span></p>
    <button class="mod_showcase_updateusers"><?php echo Text::_('MOD_SHOWCASE_UPDATE_NUSERS'); ?></button>
</div>
