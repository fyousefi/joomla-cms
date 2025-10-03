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
use Joomla\CMS\Uri\Uri;

/** @var \Joomla\Component\Users\Site\View\Remind\HtmlView $this */
/** @var Joomla\CMS\WebAsset\WebAssetManager $wa */
$wa = $this->getDocument()->getWebAssetManager();
$wa->useScript('keepalive')
    ->useScript('form.validate');
$root = rtrim(Uri::root(), '/');
$ref  = $_SERVER['HTTP_REFERER'] ?? '';
$back = (is_string($ref) && str_starts_with($ref, $root)) ? $ref : Route::_('index.php');
?>
<div class="container mt-8">
    <div class="com-users-remind remind row justify-content-center">
        <div class="col-sm-12 col-md-9 col-lg-4 p-0 fs-8">
            <?php if ($this->params->get('show_page_heading')) : ?>
                <div class="page-header">
                    <h1>
                        <?php echo $this->escape($this->params->get('page_heading')); ?>
                    </h1>
                </div>
            <?php endif; ?>

            <a href="<?php echo htmlspecialchars($back, ENT_QUOTES, 'UTF-8'); ?>" class="btn btn-link fs-2 text-decoration-none p-0">
                <i class="fa-regular fa-circle-xmark align-middle"></i>
            </a>

            <?php if ($this->params->get('menu_image') != '') : ?>
                <div class="p-2 text-center">
                    <a href="<?php echo htmlspecialchars($root, ENT_QUOTES, 'UTF-8'); ?>">
                    <?php echo HTMLHelper::_('image', $this->params->get('menu_image'), 'logo alt' , ['class' => 'w-50']); ?>
                    </a>
                </div>
            <?php endif; ?>

            <form id="user-registration" action="<?php echo Route::_('index.php?option=com_users&task=remind.remind'); ?>" method="post" class="com-users-remind__form form-validate form-horizontal well">
                <?php foreach ($this->form->getFieldsets() as $fieldset) : ?>
                    <fieldset>
                        <?php if (isset($fieldset->label)) : ?>
                            <legend class="text-justify py-3 fs-7 text-secondary"><?php echo Text::_($fieldset->label); ?></legend>
                        <?php endif; ?>
                        <?php echo $this->form->renderFieldset($fieldset->name); ?>
                    </fieldset>
                <?php endforeach; ?>
                <div class="com-users-remind__submit control-group text-center">
                    <div class="controls d-grid gap-2 pb-2">
                        <button type="submit" class="btn btn-danger fs-8 validate">
                            <?php echo Text::_('JSUBMIT'); ?>
                        </button>
                    </div>
                </div>
                <?php echo HTMLHelper::_('form.token'); ?>
            </form>
        </div>
    </div>
</div>
