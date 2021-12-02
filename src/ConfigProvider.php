<?php

declare(strict_types=1);

namespace Laminas\Filter;

class ConfigProvider
{
    /**
     * Return configuration for this component.
     *
     * @return array
     */
    public function __invoke()
    {
        return [
            'dependencies' => $this->getDependencyConfig(),
        ];
    }

    /**
     * Return dependency mappings for this component.
     *
     * @return array
     */
    public function getDependencyConfig()
    {
        return [
            'aliases'   => [
                'FilterManager' => FilterPluginManager::class,

                // Legacy Zend Framework aliases
                \Zend\Filter\FilterPluginManager::class => FilterPluginManager::class,
            ],
            'factories' => [
                FilterPluginManager::class => FilterPluginManagerFactory::class,
            ],
        ];
    }
}
