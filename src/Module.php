<?php

declare(strict_types=1);

namespace Laminas\Filter;

use Laminas\ServiceManager\ServiceManager;

/**
 * @psalm-import-type ServiceManagerConfiguration from ServiceManager
 */
final class Module
{
    /**
     * Return default laminas-filter configuration for laminas-mvc applications.
     *
     * @return array{service_manager: ServiceManagerConfiguration}
     */
    public function getConfig(): array
    {
        $provider = new ConfigProvider();

        return [
            'service_manager' => $provider->getDependencyConfig(),
        ];
    }
}
