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
use Joomla\CMS\Uri\Uri;

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

$root = rtrim(Uri::root(), '/');
$ref  = $_SERVER['HTTP_REFERER'] ?? '';
$back = (is_string($ref) && str_starts_with($ref, $root)) ? $ref : Route::_('index.php');

?>

<div class="container mt-8">
    <div class="com-users-login login row justify-content-center">
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

            <?php if (($this->params->get('logindescription_show') == 1 && trim($this->params->get('login_description', ''))) || $this->params->get('login_image') != '') : ?>
            <div class="com-users-login__description login-description p-2 text-center">
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

            <form action="<?php echo Route::_('index.php?option=com_users&task=user.login'); ?>" method="post" class="com-users-login__form form-validate form-horizontal well " id="com-users-login__form">

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
                            <button type="submit" class="btn btn-danger fs-8" style="--bs-btn-hover-bg: #000;">
                                <?php echo Text::_('JLOGIN'); ?>
                            </button>
                        </div>
                    </div>
                    <?php $return = $this->form->getValue('return', '', $this->params->get('login_redirect_url', $this->params->get('login_redirect_menuitem', ''))); ?>
                    <input type="hidden" name="return" value="<?php echo base64_encode($return); ?>">
                    <?php echo HTMLHelper::_('form.token'); ?>
                </fieldset>
            </form>
            <div style="grid-template-columns: 1fr 1fr;" class="d-grid gap-0 column-gap-5 py-4">
                <a class="com-users-login__reset link-danger" href="<?php echo Route::_('index.php?option=com_users&view=reset'); ?>">
                    <?php echo Text::_('COM_USERS_LOGIN_RESET'); ?>
                </a>
                <a class="com-users-login__remind link-danger" href="<?php echo Route::_('index.php?option=com_users&view=remind'); ?>">
                    <?php echo Text::_('COM_USERS_LOGIN_REMIND'); ?>
                </a>
            </div>

            <?php if ($usersConfig->get('allowUserRegistration')) : ?>
            <div class="position-relative mx-auto my-5" style="max-width:520px;">
                <div class="position-absolute top-0 start-50 translate-middle rounded-circle bg-danger text-white d-flex align-items-center justify-content-center shadow z-1"
                     style="width:72px;height:72px;">
                    <i class="fa fa-user-plus fs-2 align-middle" aria-hidden="true"></i>
                </div>
                <div class="card rounded-4 shadow-sm border-0 bg-body-secondary">
                    <div class="card-body text-center px-4 py-5">
                        <p class="fs-6 m-0">
                            <?php echo Text::_('TPL_HWDCITY_ASK_ACC'); ?>
                            <a href="<?php echo Route::_('index.php?option=com_users&view=registration'); ?>" class="link-danger fw-bold text-decoration-none">
                                <?php echo Text::_('TPL_HWDCITY_SIGN_UP'); ?>
                            </a>
                        </p>
                    </div>
                </div>
            </div>
            <?php endif; ?>

        </div>
    </div>
</div>
