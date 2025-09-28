<?php

/**
 * @package     Joomla.Site
 * @subpackage  com_users
 *
 * @copyright   (C) 2009 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Router\Route;

/** @var \Joomla\Component\Users\Site\View\Login\HtmlView $this */

/** @var Joomla\CMS\WebAsset\WebAssetManager $wa */
$wa = $this->getDocument()->getWebAssetManager();
$wa->useScript('keepalive')
    ->useScript('form.validate');

$usersConfig = ComponentHelper::getParams('com_users');

$input = Factory::getApplication()->getInput();
$option = $input->getCmd('option', '');
$view   = $input->getCmd('view', '');
$loginView = ($option === 'com_users' && $view === 'login');

?>

<div class="container mt-8">
    <div class="com-users-login login row justify-content-center">
    <div class="col-sm-12 col-md-9 col-lg-4 bg-body border border-1 rounded p-0 fs-8">
    <?php if ($this->params->get('show_page_heading')) : ?>
    <div class="page-header">
        <h1>
            <?php echo $this->escape($this->params->get('page_heading')); ?>
        </h1>
    </div>
    <?php endif; ?>

    <?php if (($this->params->get('logindescription_show') == 1 && trim($this->params->get('login_description', ''))) || $this->params->get('login_image') != '') : ?>
    <div class="com-users-login__description login-description bg-secondary p-2 text-center">
    <?php endif; ?>

        <?php if ($this->params->get('logindescription_show') == 1) : ?>
            <?php echo $this->params->get('login_description'); ?>
        <?php endif; ?>

        <?php if ($this->params->get('login_image') != '') : ?>
            <?php echo HTMLHelper::_('image', $this->params->get('login_image'), empty($this->params->get('login_image_alt')) && empty($this->params->get('login_image_alt_empty')) ? false : $this->params->get('login_image_alt'), ['class' => 'com-users-login__image login-image w-50']); ?>
        <?php endif; ?>

    <?php if (($this->params->get('logindescription_show') == 1 && trim($this->params->get('login_description', ''))) || $this->params->get('login_image') != '') : ?>
    </div>
    <?php endif; ?>

    <form action="<?php echo Route::_('index.php?option=com_users&task=user.login'); ?>" method="post" class="com-users-login__form form-validate form-horizontal well px-5" id="com-users-login__form">

        <fieldset>
            <?php echo $this->form->renderFieldset('credentials', ['class' => 'com-users-login__input rounded-0']); ?>

            <?php if (PluginHelper::isEnabled('system', 'remember')) : ?>
                <div class="com-users-login__remember">
                    <div class="form-check">
                        <input class="form-check-input" id="remember" type="checkbox" name="remember" value="yes">
                        <label class="form-check-label" for="remember">
                            <?php echo Text::_('COM_USERS_LOGIN_REMEMBER_ME'); ?>
                        </label>
                    </div>
                </div>
            <?php endif; ?>

            <?php foreach ($this->extraButtons as $button) :
                $dataAttributeKeys = array_filter(array_keys($button), function ($key) {
                    return substr($key, 0, 5) == 'data-';
                });
                ?>
                <div class="com-users-login__submit control-group">
                    <div class="controls">
                        <button type="button"
                                class="btn btn-secondary w-100 <?php echo $button['class'] ?? '' ?>"
                                <?php foreach ($dataAttributeKeys as $key) : ?>
                                    <?php echo $key ?>="<?php echo $button[$key] ?>"
                                <?php endforeach; ?>
                                <?php if ($button['onclick']) : ?>
                                onclick="<?php echo $button['onclick'] ?>"
                                <?php endif; ?>
                                title="<?php echo Text::_($button['label']) ?>"
                                id="<?php echo $button['id'] ?>"
                        >
                            <?php if (!empty($button['icon'])) : ?>
                                <span class="<?php echo $button['icon'] ?>"></span>
                            <?php elseif (!empty($button['image'])) : ?>
                                <?php echo HTMLHelper::_('image', $button['image'], Text::_($button['tooltip'] ?? ''), [
                                    'class' => 'icon',
                                ], true) ?>
                            <?php elseif (!empty($button['svg'])) : ?>
                                <?php echo $button['svg']; ?>
                            <?php endif; ?>
                            <?php echo Text::_($button['label']) ?>
                        </button>
                    </div>
                </div>
            <?php endforeach; ?>

            <div class="com-users-login__submit control-group text-center mb-0">
                <div class="controls d-grid gap-2 pb-2">
                    <button type="submit" class="btn btn-danger fs-8 rounded-0" style="--bs-btn-hover-bg: #000;">
                        <?php echo Text::_('JLOGIN'); ?>
                    </button>
                </div>

                <hr class="border border-secondary border-1 opacity-25">

                <a href="<?php JRoute::_('index.php') ?>" class="btn btn-link fs-8 rounded-0 text-decoration-none fs-9 p-0">
                    <?php echo Text::_('TPL_HWDCITY_BACK'); ?>
                    <i class="fa fa-arrow-left align-middle"></i>
                </a>
            </div>
            <?php $return = $this->form->getValue('return', '', $this->params->get('login_redirect_url', $this->params->get('login_redirect_menuitem', ''))); ?>
            <input type="hidden" name="return" value="<?php echo base64_encode($return); ?>">
            <?php echo HTMLHelper::_('form.token'); ?>
        </fieldset>
    </form>
    <div class="com-users-login__options list-group ">
        <a class="com-users-login__reset list-group-item border border-bottom-0 border-end-0 border-start-0 rounded-0" href="<?php echo Route::_('index.php?option=com_users&view=reset'); ?>">
            <?php echo Text::_('COM_USERS_LOGIN_RESET'); ?>
        </a>
        <a class="com-users-login__remind list-group-item border border-bottom-0 border-end-0 border-start-0 rounded-0" href="<?php echo Route::_('index.php?option=com_users&view=remind'); ?>">
            <?php echo Text::_('COM_USERS_LOGIN_REMIND'); ?>
        </a>
        <?php if ($usersConfig->get('allowUserRegistration')) : ?>
            <a class="com-users-login__register list-group-item border border-bottom-0 border-end-0 border-start-0 rounded-0" href="<?php echo Route::_('index.php?option=com_users&view=registration'); ?>">
                <?php echo Text::_('COM_USERS_LOGIN_REGISTER'); ?>
            </a>
        <?php endif; ?>
    </div>
    </div>
</div>
</div>
