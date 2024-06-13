<?php

declare(strict_types=1);

namespace Laminas\Filter;

use Laminas\ServiceManager\ServiceManager;
use Psr\Container\ContainerInterface;

use function is_array;

/**
 * @psalm-import-type ServiceManagerConfiguration from ServiceManager
 */
final class FilterPluginManagerFactory
{
    public function __invoke(ContainerInterface $container): FilterPluginManager
    {
        // If this is in a laminas-mvc application, the ServiceListener will inject
        // merged configuration during bootstrap.
        if ($container->has('ServiceListener')) {
            return new FilterPluginManager($container);
        }

        // If we do not have a config service, nothing more to do
        if (! $container->has('config')) {
            return new FilterPluginManager($container);
        }

        $config = $container->get('config');

        // If we do not have filters configuration, nothing more to do
        if (! isset($config['filters']) || ! is_array($config['filters'])) {
            return new FilterPluginManager($container);
        }

        /** @psalm-var ServiceManagerConfiguration $config['filters'] */

        return new FilterPluginManager($container, $config['filters']);
    }
}
