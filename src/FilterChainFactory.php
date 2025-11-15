<?php

declare(strict_types=1);

namespace Laminas\Filter;

use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;

use function assert;

/** @psalm-import-type FilterChainConfiguration from FilterChain */
final class FilterChainFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, string $requestedName, ?array $options = null): FilterChain
    {
        /**
         * Runtime validation of the chain spec can be done but is not because it would introduce a BC break
         *
         * @see FilterChain::validateSpecification()
         *
         * @psalm-var FilterChainConfiguration $options
         */
        $options       = $options ?? [];
        $pluginManager = $container->get(FilterPluginManager::class);
        assert($pluginManager instanceof FilterPluginManager);

        return new FilterChain($pluginManager, $options);
    }
}
