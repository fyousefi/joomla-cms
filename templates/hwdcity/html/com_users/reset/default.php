<?php

/**
 * @package     Joomla.Site
 * @subpackage  com_users
 *
 * @copyright   (C) 2009 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;

/** @var \Joomla\Component\Users\Site\View\Reset\HtmlView $this */
/** @var Joomla\CMS\WebAsset\WebAssetManager $wa */
$wa = $this->getDocument()->getWebAssetManager();
$wa->useScript('keepalive')
    ->useScript('form.validate');

?>
<div class="container mt-8">
    <div class="com-users-reset reset row justify-content-center">
        <div class="col-sm-12 col-md-9 col-lg-4 bg-body border border-1 rounded p-0 fs-8">
    <?php if ($this->params->get('show_page_heading')) : ?>
        <div class="page-header">
            <h1>
                <?php echo $this->escape($this->params->get('page_heading')); ?>
            </h1>
        </div>
    <?php endif; ?>

    <?php if ($this->params->get('menu_image') != '') : ?>
        <div class="bg-secondary p-2 text-center">
            <?php echo HTMLHelper::_('image', $this->params->get('menu_image'), 'logo alt' , ['class' => 'w-50']); ?>
        </div>
    <?php endif; ?>

    <form id="user-registration" action="<?php echo Route::_('index.php?option=com_users&task=reset.request'); ?>" method="post" class="com-users-reset__form form-validate form-horizontal well px-5">
        <?php foreach ($this->form->getFieldsets() as $fieldset) : ?>
            <fieldset>
                <?php if (isset($fieldset->label)) : ?>
                    <legend class="text-justify py-3 fs-13 text-secondary"><?php echo Text::_($fieldset->label); ?></legend>
                <?php endif; ?>
                <?php echo $this->form->renderFieldset($fieldset->name); ?>
            </fieldset>
        <?php endforeach; ?>
        <div class="com-users-reset__submit control-group text-center">
            <div class="controls d-grid gap-2 pb-2">
                <button type="submit" class="btn btn-danger fs-8 rounded-0 validate">
                    <?php echo Text::_('JSUBMIT'); ?>
                </button>
            </div>
            <hr class="border border-secondary border-1 opacity-25">
            <a href="<?php JRoute::_('index.php') ?>" class="btn btn-link fs-8 rounded-0 text-decoration-none fs-9 p-0">
                <?php echo Text::_('TPL_HWDCITY_BACK'); ?>
                <i class="fa fa-arrow-left align-middle"></i>
            </a>
        </div>
        <?php echo HTMLHelper::_('form.token'); ?>
    </form>
        </div>
    </div>
</div>
