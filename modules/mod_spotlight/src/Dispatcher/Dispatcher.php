<?php

namespace AsiaSun\Module\Spotlight\Site\Dispatcher;

\defined('_JEXEC') or die;

use Joomla\CMS\Dispatcher\AbstractModuleDispatcher;
use Joomla\CMS\Helper\HelperFactoryAwareInterface;
use Joomla\CMS\Helper\HelperFactoryAwareTrait;
use Joomla\CMS\Helper\ModuleHelper;

class Dispatcher extends AbstractModuleDispatcher implements HelperFactoryAwareInterface
{
    use HelperFactoryAwareTrait;

    protected function getLayoutData(): array
    {
        $data   = parent::getLayoutData();
        $params = $data['params'];

        $cacheParams               = new \stdClass();
        $cacheParams->cachemode    = 'safeuri';
        $cacheParams->class        = $this->getHelperFactory()->getHelper('SpotlightHelper');
        $cacheParams->method       = 'getItems';
        $cacheParams->methodparams = [$params];
        $cacheParams->modeparams   = [];

        $data['items'] = ModuleHelper::moduleCache($this->module, $params, $cacheParams);
        $data['heading'] = $params->get('heading', 'h4');

        return $data;
    }
}
