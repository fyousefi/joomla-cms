<?php

/**
 * @package     Joomla.Site
 * @subpackage  Templates.hwdcity
 *
 * @copyright   (C) 2025 AsiaSun.ir, Pvt. Ltd. <https://www.asiasun.ir>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Session\Session;

/** @var Joomla\CMS\Document\HtmlDocument $this */

$app   = Factory::getApplication();
$input = $app->getInput();
$wa    = $this->getWebAssetManager();
$scrollSidebars = (bool) $this->params->get('sidebarScroll', 0);

// Browsers support SVG favicons
$this->addHeadLink(HTMLHelper::_('image', 'joomla-favicon.svg', '', [], true, 1), 'icon', 'rel', ['type' => 'image/svg+xml']);
$this->addHeadLink(HTMLHelper::_('image', 'favicon.ico', '', [], true, 1), 'alternate icon', 'rel', ['type' => 'image/vnd.microsoft.icon']);
$this->addHeadLink(HTMLHelper::_('image', 'joomla-favicon-pinned.svg', '', [], true, 1), 'mask-icon', 'rel', ['color' => '#000']);

// Detecting Active Variables
$option   = $input->getCmd('option', '');
$view     = $input->getCmd('view', '');
$layout   = $input->getCmd('layout', '');
$task     = $input->getCmd('task', '');
$itemid   = $input->getCmd('Itemid', '');
$sitename = htmlspecialchars($app->get('sitename'), ENT_QUOTES, 'UTF-8');
$menu     = $app->getMenu()->getActive();
$pageclass = $menu !== null ? $menu->getParams()->get('pageclass_sfx', '') : '';

// Color Theme
$paramsColorName = $this->params->get('colorName', 'colors_standard');
$assetColorName  = 'theme.' . $paramsColorName;

// Use a font scheme if set in the template style options
$paramsFontScheme = $this->params->get('useFontScheme', false);
$fontStyles       = '';
$isUserLayout     = ($option == 'com_users') ? 'd-none' : '';

// If the active menu's Page Class contains "full-width", use full-width; else default container
$isFullLayout = (strpos(' ' . (string)$pageclass . ' ', ' full-width ') !== false)
    ? 'full-width'
    : 'grid-child container-component';

if ($paramsFontScheme) {
    if (stripos($paramsFontScheme, 'https://') === 0) {
        $this->getPreloadManager()->preconnect('https://fonts.googleapis.com/', ['crossorigin' => 'anonymous']);
        $this->getPreloadManager()->preconnect('https://fonts.gstatic.com/', ['crossorigin' => 'anonymous']);
        $this->getPreloadManager()->preload($paramsFontScheme, ['as' => 'style', 'crossorigin' => 'anonymous']);
        $wa->registerAndUseStyle('fontscheme.current', $paramsFontScheme, [], ['rel' => 'lazy-stylesheet', 'crossorigin' => 'anonymous']);

        if (preg_match_all('/family=([^?:]*):/i', $paramsFontScheme, $matches) > 0) {
            $fontStyles = '--hwdcity-font-family-body: "' . str_replace('+', ' ', $matches[1][0]) . '", sans-serif;
			--hwdcity-font-family-headings: "' . str_replace('+', ' ', $matches[1][1] ?? $matches[1][0]) . '", sans-serif;
			--hwdcity-font-weight-normal: 300;
			--hwdcity-font-weight-headings: 700;';
        }
    } elseif ($paramsFontScheme === 'system') {
        $fontStylesBody    = $this->params->get('systemFontBody', '');
        $fontStylesHeading = $this->params->get('systemFontHeading', '');

        if ($fontStylesBody) {
            $fontStyles = '--hwdcity-font-family-body: ' . $fontStylesBody . ';
            --hwdcity-font-weight-normal: 300;';
        }
        if ($fontStylesHeading) {
            $fontStyles .= '--hwdcity-font-family-headings: ' . $fontStylesHeading . ';
    		--hwdcity-font-weight-headings: 700;';
        }
    } else {
        $wa->registerAndUseStyle('fontscheme.current', $paramsFontScheme, ['version' => 'auto'], ['rel' => 'lazy-stylesheet']);
        $this->getPreloadManager()->preload($wa->getAsset('style', 'fontscheme.current')->getUri() . '?' . $this->getMediaVersion(), ['as' => 'style']);
    }
}

// Enable assets
$wa->usePreset('template.hwdcity.' . ($this->direction === 'rtl' ? 'rtl' : 'ltr'))
    ->useStyle('template.active.language')
    ->registerAndUseStyle($assetColorName, 'global/' . $paramsColorName . '.css')
    ->useStyle('template.user')
    ->useScript('template.user')
    ->addInlineStyle(":root {
		--hue: 214;
		--template-bg-light: #f0f4fb;
		--template-text-dark: #495057;
		--template-text-light: #ffffff;
		--template-link-color: var(--link-color);
		--template-special-color: #001B4C;
		$fontStyles
	}");

// Override 'template.active' asset to set correct ltr/rtl dependency
$wa->registerStyle('template.active', '', [], [], ['template.hwdcity.' . ($this->direction === 'rtl' ? 'rtl' : 'ltr')]);

// Logo file or site title param
if ($this->params->get('logoFile')) {
    $logo = HTMLHelper::_('image', Uri::root(false) . htmlspecialchars($this->params->get('logoFile'), ENT_QUOTES), $sitename, ['loading' => 'eager', 'decoding' => 'async'], false, 0);
} elseif ($this->params->get('siteTitle')) {
    $logo = '<span title="' . $sitename . '">' . htmlspecialchars($this->params->get('siteTitle'), ENT_COMPAT, 'UTF-8') . '</span>';
} else {
    $logo = HTMLHelper::_('image', 'logo.png', $sitename, ['class' => 'logo d-inline-block', 'loading' => 'eager', 'decoding' => 'async'], true, 0);
}

$hasClass = '';

if ($this->countModules('sidebar-left', true)) {
    $hasClass .= ' has-sidebar-left';
}

if ($this->countModules('sidebar-right', true)) {
    $hasClass .= ' has-sidebar-right';
}

// Container
$wrapper = $this->params->get('fluidContainer') ? 'wrapper-fluid' : 'wrapper-static';

$this->setMetaData('viewport', 'width=device-width, initial-scale=1');

$stickyHeader = $this->params->get('stickyHeader') ? 'position-sticky sticky-top' : '';

// Defer fontawesome for increased performance. Once the page is loaded javascript changes it to a stylesheet.
$wa->getAsset('style', 'fontawesome')->setAttribute('rel', 'lazy-stylesheet');

// TOC offcanvas
$ocData = $app->getUserState('plg.offcanvasbreak.data');
$bodyOverflowFix = !empty($ocData) ? ' overflow-x-hidden' : '';

?>
<!DOCTYPE html>
<html lang="<?php echo $this->language; ?>" dir="<?php echo $this->direction; ?>">

<head>
    <jdoc:include type="metas" />
    <jdoc:include type="styles" />
    <jdoc:include type="scripts" />
</head>

<body class="site ss02 <?php echo $option
    . ' ' . $wrapper
    . ' view-' . $view
    . ($layout ? ' layout-' . $layout : ' no-layout')
    . ($task ? ' task-' . $task : ' no-task')
    . ($itemid ? ' itemid-' . $itemid : '')
    . ($pageclass ? ' ' . $pageclass : '')
    . $hasClass
    . $bodyOverflowFix
    . ($this->direction == 'rtl' ? ' rtl' : '');
?>">
<header class="header container-header full-width <?php echo $stickyHeader ? ' ' . $stickyHeader : ''; echo $isUserLayout ?>">

    <?php if ($this->countModules('topbar')) : ?>
        <div class="container-topbar">
            <jdoc:include type="modules" name="topbar" style="none" />
        </div>
    <?php endif; ?>

    <?php if ($this->countModules('below-top')) : ?>
        <div class="grid-child container-below-top">
            <jdoc:include type="modules" name="below-top" style="none" />
        </div>
    <?php endif; ?>

    <?php if ($this->params->get('brand', 1)) : ?>
        <div class="grid-child d-none d-lg-block">
            <div class="d-flex flex-column-reverse flex-lg-row justify-content-between align-items-center align-items-lg-start gap-3 w-100">
                <!-- Banner: below logo on mobile, left side on desktop -->
                <?php if ($this->countModules('logo-ads', true)) : ?>
                    <div class="order-1 order-lg-2 text-center text-lg-end w-lg-auto">
                        <jdoc:include type="modules" name="logo-ads" style="none" />
                    </div>
                <?php endif; ?>

                <!-- Logo (fixed position, right-aligned on desktop) -->
                <div class="order-2 order-lg-1">
                    <div class="navbar-brand">
                        <a class="brand-logo" href="<?php echo $this->baseurl; ?>/">
                            <?php echo $logo; ?>
                        </a>
                        <?php if ($this->params->get('siteDescription')) : ?>
                            <div class="site-description"><?php echo htmlspecialchars($this->params->get('siteDescription')); ?></div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>
</header>

<?php if ($this->countModules('menu', true) || $this->countModules('search', true)) : ?>
    <div class="container-nav bg-black d-flex align-items-baseline px-lg-10 px-3 pb-md-2 pb-lg-0 sticky-top">

        <div class="mobile-logo d-lg-none ps-5 pt-3">
            <a href="<?php echo $this->baseurl; ?>/">
                <?php echo $logo; ?>
            </a>
        </div>

        <?php if ($this->countModules('menu', true)) : ?>
            <jdoc:include type="modules" name="menu" style="none" />
        <?php endif; ?>

        <?php if ($this->countModules('search', true)) : ?>
            <div class="container-search">
                <jdoc:include type="modules" name="search" style="none" />
            </div>
        <?php endif; ?>

        <div class="d-flex align-items-center ms-auto gap-3">
            <?php
            // -------- Auth link --------
            static $authLinkPrinted = false;
            if (!$authLinkPrinted) {
                $menus = $app->getMenu();     // menu object (keep separate from $menu active item)
                $user  = $app->getIdentity();

                // Hidden menu config
                $AUTH_MENUTYPE     = 'auth';
                $AUTH_LOGIN_ALIAS  = 'login';
                $AUTH_LOGOUT_ALIAS = 'logout';
                $AUTH_PROFILE_ALIAS = 'profile';

                // Helper: find by menutype + alias
                $findByTypeAlias = function($menutype, $alias) use ($menus) {
                    return ($menutype && $alias)
                        ? $menus->getItems(['menutype','alias'], [$menutype,$alias], true)
                        : null;
                };

                // Helper: fallback pattern matcher
                $findByLinkPattern = function($pattern) use ($menus) {
                    $items = $menus->getItems('component', 'com_users');
                    if (!$items) return null;
                    foreach ($items as $it) {
                        if (!empty($it->link) && stripos($it->link, $pattern) !== false) {
                            return $it;
                        }
                    }
                    return null;
                };

                // Resolve menu items automatically
                $loginItem  = $findByTypeAlias($AUTH_MENUTYPE, $AUTH_LOGIN_ALIAS)
                    ?: $findByLinkPattern('view=login');

                $logoutItem = $findByTypeAlias($AUTH_MENUTYPE, $AUTH_LOGOUT_ALIAS)
                    ?: $findByLinkPattern('layout=logout');

                $profileItem = $findByTypeAlias($AUTH_MENUTYPE, $AUTH_PROFILE_ALIAS)
                    ?: $findByLinkPattern('view=profile');

                // Respect "Display Menu Item Title"
                $loginShowTitle  = $loginItem  ? (bool) $loginItem->getParams()->get('menu_text', 1)  : true;
                $logoutShowTitle = $logoutItem ? (bool) $logoutItem->getParams()->get('menu_text', 1) : true;
                $profileShowTitle = $profileItem ? (bool) $profileItem->getParams()->get('menu_text', 1) : true;

                // Build login link + label/icon
                if ($loginItem) {
                    $loginUrl   = Route::_('index.php?Itemid=' . (int)$loginItem->id);
                    $loginLabel = htmlspecialchars($loginItem->title, ENT_QUOTES, 'UTF-8');
                    $loginIcon  = $loginItem->getParams()->get('menu-anchor_css') ?: 'fa-solid fa-right-to-bracket';
                } else {
                    $loginUrl   = Route::_('index.php?option=com_users&view=login');
                    $loginLabel = 'Login';
                    $loginIcon  = 'fa-solid fa-right-to-bracket';
                }

                // Build logout link + label/icon
                if ($logoutItem) {
                    $logoutUrl   = Route::_('index.php?Itemid=' . (int)$logoutItem->id);
                    $logoutLabel = htmlspecialchars($logoutItem->title, ENT_QUOTES, 'UTF-8');
                    $logoutIcon  = $logoutItem->getParams()->get('menu-anchor_css') ?: 'fa-solid fa-right-from-bracket';
                } else {
                    // Fallback task-based logout
                    $contextItemid = $loginItem ? (int)$loginItem->id : 0;
                    $logoutUrl   = Route::_('index.php?option=com_users&task=user.logout'
                        . ($contextItemid ? '&Itemid=' . $contextItemid : '')
                        . '&' . Session::getFormToken() . '=1'
                        . '&return=' . base64_encode(Uri::current()));
                    $logoutLabel = 'Logout';
                    $logoutIcon  = 'fa-solid fa-right-from-bracket';
                }

                // Build profile link + label/icon
                if ($profileItem) {
                    $profileUrl   = Route::_('index.php?Itemid=' . (int)$profileItem->id);
                    $profileLabel = htmlspecialchars($profileItem->title, ENT_QUOTES, 'UTF-8');
                    $profileIcon  = $profileItem->getParams()->get('menu-anchor_css') ?: 'fa-solid fa-user';
                } else {
                    $profileUrl   = Route::_('index.php?option=com_users&view=profile');
                    $profileLabel = 'Profile';
                    $profileIcon  = 'fa-solid fa-user';
                }

                // Render (white link with subtle gray hover)
                if ($user && !$user->guest) {
                    // Logout
                    echo '<a href="' . $logoutUrl . '" class="link-light link-opacity-75-hover text-decoration-none d-flex align-items-center gap-1">'
                        .      '<i class="' . htmlspecialchars($logoutIcon, ENT_QUOTES, 'UTF-8') . '"></i>'
                        .      ($logoutShowTitle ? '<span class="d-none d-md-inline fs-8 fw-bold">' . $logoutLabel . '</span>' : '')
                        .  '</a>';

                    // Profile (left of Logout), me-3 add spacing between Profile and Logout.
                    echo '<a href="' . $profileUrl . '" class="link-light link-opacity-75-hover text-decoration-none d-flex align-items-center gap-1">'
                        .      '<i class="' . htmlspecialchars($profileIcon, ENT_QUOTES, 'UTF-8') . '"></i>'
                        .      ($profileShowTitle ? '<span class="d-none d-md-inline fs-8 fw-bold">' . $profileLabel . '</span>' : '')
                        .  '</a>';
                } else {
                    // Login (only when guest)
                    echo '<a href="' . $loginUrl . '" class="link-light link-opacity-75-hover text-decoration-none d-flex align-items-center gap-1">'
                        .      '<i class="' . htmlspecialchars($loginIcon, ENT_QUOTES, 'UTF-8') . '"></i>'
                        .      ($loginShowTitle ? '<span class="d-none d-md-inline fs-8 fw-bold">' . $loginLabel . '</span>' : '')
                        .  '</a>';
                }

                $authLinkPrinted = true;
            }
            ?>
        </div>
    </div>

    <?php if ($isFullLayout === 'full-width' && $view === 'article'): ?>
        <div class="container-nav d-none d-lg-flex bg-black d-flex align-items-baseline px-lg-10 px-3 pb-md-2 pb-lg-0 position-fixed top-0 start-0 w-100 h-b50 z-3" id="compactBar" aria-hidden="true">
            <div class="mobile-logo pt-1">
                <a href="<?php echo $this->baseurl; ?>/"><?php echo $logo; ?></a>
            </div>

            <div class="ms-auto pt-3">
                <?php
                // TOC offcanvas
                static $ocPrintedBtn = false;
                if ($ocPrintedBtn) return;

                $data = $app->getUserState('plg.offcanvasbreak.data', []);

                if (!empty($data['sections'])) {
                    // Button only
                    $displayData = $data;  // contains articleId, sections, title
                    include PluginHelper::getLayoutPath('content', 'offcanvasbreak', 'toggler');
                    $ocPrintedBtn = true;

                    // Clear after use to avoid leaking across subsequent non-article renders
                    //$app->setUserState('plg.offcanvasbreak.data', null);
                }
                ?>
            </div>
        </div>

        <div id="readProgressTrack" class="position-fixed start-0 end-0 z-1">
            <div class="read-progress__bar" id="readProgressBar" role="presentation"></div>
        </div>
    <?php endif; ?>
<?php endif; ?>

<div class="site-grid">

    <?php if ($this->countModules('logo-ads', true) || $this->countModules('banner-top')) : ?>
        <div class="container-banner-top">
            <?php if ($this->countModules('logo-ads', true)) : ?>
                <div class="d-lg-none pt-1 d-flex justify-content-center gap-2">
                    <jdoc:include type="modules" name="logo-ads" style="none" />
                </div>
            <?php endif; ?>
            <?php if ($this->countModules('banner-top', true)) : ?>
                <div class="pt-1 d-flex flex-lg-row flex-wrap justify-content-center gap-2 ">
                    <jdoc:include type="modules" name="banner-top" style="none" />
                </div>
            <?php endif; ?>
        </div>
    <?php endif; ?>

    <?php if ($this->countModules('top-a', true)) : ?>
        <div class="grid-child container-top-a">
            <jdoc:include type="modules" name="top-a" style="card" />
        </div>
    <?php endif; ?>

    <?php if ($this->countModules('banner-mid', true)) : ?>
        <div class="container-banner-mid">
            <?php if ($this->countModules('banner-mid', true)) : ?>
                <div class="d-flex flex-lg-row justify-content-center flex-wrap gap-2 pt-1">
                    <jdoc:include type="modules" name="banner-mid" style="none" />
                </div>
            <?php endif; ?>
        </div>
    <?php endif; ?>

    <?php if ($this->countModules('top-b', true)) : ?>
        <div class="grid-child container-top-b">
            <jdoc:include type="modules" name="top-b" style="card" />
        </div>
    <?php endif; ?>

    <?php if ($this->countModules('sidebar-left', true)) : ?>
        <div class="grid-child container-sidebar-left sticky-lg-top z-3 <?php echo $scrollSidebars ? 'overflow-y-auto vh-100' : ''; ?>">
            <jdoc:include type="modules" name="left-top" style="noCard" />
            <jdoc:include type="modules" name="sidebar-left" style="card" />
        </div>
    <?php endif; ?>

    <div class="<?php echo $isFullLayout; ?>">
        <?php if ($this->countModules('hot-topic', true)): ?>
            <jdoc:include type="modules" name="hot-topic" style="none" />
        <?php endif; ?>
        <jdoc:include type="modules" name="breadcrumbs" style="none" />
        <jdoc:include type="modules" name="main-top" style="card" />

        <?php if($isUserLayout): ?>
        <div class="col-lg-6 offset-lg-3">
            <?php endif; ?>
            <jdoc:include type="message" />
            <?php if($isUserLayout): ?>
        </div>
    <?php endif; ?>

        <main>
            <jdoc:include type="component" />
        </main>
        <jdoc:include type="modules" name="main-bottom" style="card" />
    </div>

    <?php if ($this->countModules('sidebar-right', true)) : ?>
        <div class="grid-child container-sidebar-right sticky-lg-top z-3 <?php echo $scrollSidebars ? 'overflow-y-auto vh-100' : ''; ?>">
            <jdoc:include type="modules" name="right-top" style="noCard" />
            <jdoc:include type="modules" name="sidebar-right" style="card" />
        </div>
    <?php endif; ?>
</div>

<div class="site-grid">
    <?php if ($this->countModules('bottom-a', true)) : ?>
        <div class="container-bottom-a full-width">
            <jdoc:include type="modules" name="bottom-a" style="card" />
        </div>
    <?php endif; ?>

    <?php if ($this->countModules('banner-bottom', true)) : ?>
        <div class="container-banner-bottom">
            <?php if ($this->countModules('banner-bottom', true)) : ?>
                <div class="d-flex flex-lg-row justify-content-center flex-wrap gap-2 mt-2 ">
                    <jdoc:include type="modules" name="banner-bottom" style="none" />
                </div>
            <?php endif; ?>
        </div>
    <?php endif; ?>

    <?php if ($this->countModules('bottom-b', true)) : ?>
        <div class="container-bottom-b full-width">
            <jdoc:include type="modules" name="bottom-b" style="media" />
        </div>
    <?php endif; ?>
</div>

<?php if ($this->countModules('footer1', true) || $this->countModules('footer2', true) || $this->countModules('footer3', true) || $this->countModules('footer4', true)) : ?>
    <footer class="container-footer footer full-width mt-0">
        <div class="container py-4 py-md-5">
            <div class="row g-4 fs-9">
                <div class="col-12 col-lg-4"><?php echo $this->countModules('footer1') ? '<jdoc:include type="modules" name="footer1" style="spotfooter" />' : ''; ?></div>
                <div class="col-6 col-lg-2"><?php echo $this->countModules('footer2') ? '<jdoc:include type="modules" name="footer2" style="spotfooter" />' : ''; ?></div>
                <div class="col-6 col-lg-2"><?php echo $this->countModules('footer3') ? '<jdoc:include type="modules" name="footer3" style="spotfooter" />' : ''; ?></div>
                <div class="col-12 col-lg-4"><?php echo $this->countModules('footer4') ? '<jdoc:include type="modules" name="footer4" style="spotfooter" />' : ''; ?></div>
            </div>
        </div>
    </footer>
<?php endif; ?>

<?php if ($this->params->get('backTop') == 1) : ?>
    <a href="#top" id="back-top" class="back-to-top-link rounded-0 py-2 px-29 " aria-label="<?php echo Text::_('TPL_hwdcity_BACKTOTOP'); ?>">
        <span class="icon-arrow-up fs-6 icon-fw align-middle" aria-hidden="true"></span>
    </a>
<?php endif; ?>

<?php
    // Offcanvas panel
    if (!empty($data['sections'])) {
        $displayData = $data;
        include PluginHelper::getLayoutPath('content', 'offcanvasbreak', 'panel');
        // now it's safe to clear for this request
        $app->setUserState('plg.offcanvasbreak.data', null);
    }
?>

<jdoc:include type="modules" name="debug" style="none" />
</body>

</html>
