<?php

/**
 * @package     Joomla.Site
 * @subpackage  com_users
 *
 * @copyright   (C) 2009 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

\defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Uri\Uri;

/** @var \Joomla\Component\Users\Site\View\Registration\HtmlView $this */
/** @var Joomla\CMS\WebAsset\WebAssetManager $wa */
$wa = $this->getDocument()->getWebAssetManager();
$wa->useScript('keepalive')
    ->useScript('form.validate');
$root = rtrim(Uri::root(), '/');
$ref  = $_SERVER['HTTP_REFERER'] ?? '';
$back = (is_string($ref) && str_starts_with($ref, $root)) ? $ref : Route::_('index.php');
?>
<div class="container mt-8">
    <div class="com-users-registration registration row justify-content-center">
        <div class="col-sm-12 col-md-9 col-lg-4 p-0 fs-8">
    <?php if ($this->params->get('show_page_heading')) : ?>
        <div class="page-header">
            <h1><?php echo $this->escape($this->params->get('page_heading')); ?></h1>
        </div>
    <?php endif; ?>
            <a href="<?php echo htmlspecialchars($back, ENT_QUOTES, 'UTF-8'); ?>" class="btn btn-link fs-2 text-decoration-none p-0">
                <i class="fa-regular fa-circle-xmark align-middle"></i>
            </a>
    <?php if ($this->params->get('menu_image') != '') : ?>
        <div class="p-2 text-center">
            <?php echo HTMLHelper::_('image', $this->params->get('menu_image'), 'logo alt' , ['class' => 'w-50']); ?>
        </div>
    <?php endif; ?>

    <form id="member-registration" action="<?php echo Route::_('index.php?option=com_users&task=registration.register'); ?>" method="post" class="com-users-registration__form form-validate px-5" enctype="multipart/form-data">
        <?php // Iterate through the form fieldsets and display each one.?>
        <?php foreach ($this->form->getFieldsets() as $fieldset) : ?>
            <?php if ($fieldset->name === 'captcha' && $this->captchaEnabled) : ?>
                <?php continue; ?>
            <?php endif; ?>
            <?php $fields = $this->form->getFieldset($fieldset->name); ?>
            <?php if (\count($fields)) : ?>
                <fieldset>
                    <?php // If the fieldset has a label set, display it as the legend.?>
                    <?php if (isset($fieldset->label)) : ?>
                        <legend class="text-center py-3 fs-5 text-secondary"><?php echo Text::_($fieldset->label); ?></legend>
                        <hr class="border border-secondary border-1 opacity-0">
                    <?php endif; ?>
                    <?php echo $this->form->renderFieldset($fieldset->name); ?>
                </fieldset>
            <?php endif; ?>
        <?php endforeach; ?>
        <?php if ($this->captchaEnabled) : ?>
            <?php echo $this->form->renderFieldset('captcha'); ?>
        <?php endif; ?>
        <div class="com-users-registration__submit control-group text-center">
            <div class="controls d-grid gap-2 pb-2">
                <button type="submit" class="com-users-registration__register btn btn-danger fs-8 validate">
                    <?php echo Text::_('TPL_HWDCITY_JREGISTER'); ?>
                </button>
                <input type="hidden" name="option" value="com_users">
                <input type="hidden" name="task" value="registration.register">
            </div>
            <hr class="border border-secondary border-1 opacity-25">
        </div>
        <?php echo HTMLHelper::_('form.token'); ?>
    </form>
        </div>
    </div>
</div>
