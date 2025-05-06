<?php

declare(strict_types=1);

namespace Laminas\Filter;

use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;

use function assert;

/** @psalm-import-type ChainSpec from ImmutableFilterChain */
final class ImmutableFilterChainFactory implements FactoryInterface
{
    public function __invoke(
        ContainerInterface $container,
        string $requestedName,
        ?array $options = null,
    ): ImmutableFilterChain {
        /**
         * It's not worth attempting runtime validation of the specification shape
         * @psalm-var ChainSpec $options
         */
        $options       = $options ?? [];
        $pluginManager = $container->get(FilterPluginManager::class);
        assert($pluginManager instanceof FilterPluginManager);

        return ImmutableFilterChain::fromArray($options, $pluginManager);
    }
}
