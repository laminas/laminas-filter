<?php

declare(strict_types=1);

namespace Laminas\Filter;

use Laminas\ServiceManager\ConfigInterface;

/**
 * @psalm-import-type ServiceManagerConfigurationType from ConfigInterface
 */
class ConfigProvider
{
    /**
     * Return configuration for this component.
     *
     * @return array<string, mixed>
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
     * @return ServiceManagerConfigurationType
     */
    public function getDependencyConfig()
    {
        return [
            'aliases'   => [
                'FilterManager' => FilterPluginManager::class,

                // Legacy Zend Framework aliases
                'Zend\Filter\FilterPluginManager' => FilterPluginManager::class,
            ],
            'factories' => [
                FilterPluginManager::class => FilterPluginManagerFactory::class,
            ],
        ];
    }
}
