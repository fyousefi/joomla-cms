<?php
namespace AsiaSun\Module\Showcase\Site\Dispatcher;

\defined('_JEXEC') or die;

use Joomla\CMS\Dispatcher\DispatcherInterface;
use Joomla\CMS\Helper\ModuleHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Application\CMSApplicationInterface;
use Joomla\Input\Input;
use Joomla\Registry\Registry;
use AsiaSun\Module\Showcase\Site\Helper\ShowcaseHelper;

class Dispatcher implements DispatcherInterface
{
    protected $module;

    protected $app;

    public function __construct(\stdClass $module, CMSApplicationInterface $app, Input $input)
    {
        $this->module = $module;
        $this->app = $app;
    }

    public function dispatch()
    {
        $language = $this->app->getLanguage();
        $language->load('mod_showcase', JPATH_BASE . '/modules/mod_showcase');

        $username = ShowcaseHelper::getLoggedonUsername('Guest');

        $hello = Text::_('MOD_SHOWCASE_GREETING') . $username;

        $params = new Registry($this->module->params);

        require ModuleHelper::getLayoutPath('mod_showcase');
    }
}
