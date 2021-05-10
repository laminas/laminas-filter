<?php

namespace Laminas\Filter;

class Module
{
    /**
     * Return default laminas-filter configuration for laminas-mvc applications.
     */
    public function getConfig()
    {
        $provider = new ConfigProvider();

        return [
            'service_manager' => $provider->getDependencyConfig(),
        ];
    }

    /**
     * Register a specification for the FilterManager with the ServiceListener.
     *
     * @param \Laminas\ModuleManager\ModuleManager $moduleManager
     * @return void
     */
    public function init($moduleManager)
    {
        $event = $moduleManager->getEvent();
        $container = $event->getParam('ServiceManager');
        $serviceListener = $container->get('ServiceListener');

        $serviceListener->addServiceManager(
            'FilterManager',
            'filters',
            FilterProviderInterface::class,
            'getFilterConfig'
        );
    }
}
