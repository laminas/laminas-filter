<?php

declare(strict_types=1);

namespace LaminasTest\Filter;

use Laminas\Filter\FilterPluginManager;
use Laminas\Filter\FilterPluginManagerFactory;
use Laminas\Filter\ImmutableFilterChain;
use Laminas\Filter\StringToLower;
use Laminas\ServiceManager\ServiceManager;
use PHPUnit\Framework\TestCase;

final class ImmutableFilterChainFactoryTest extends TestCase
{
    private FilterPluginManager $pluginManager;

    protected function setUp(): void
    {
        $serviceManager = new ServiceManager([
            'factories' => [
                FilterPluginManager::class => FilterPluginManagerFactory::class,
            ],
        ]);

        $this->pluginManager = $serviceManager->get(FilterPluginManager::class);
    }

    public function testAFilterChainCanBeRetrievedFromThePluginManager(): void
    {
        $chain = $this->pluginManager->get(ImmutableFilterChain::class);

        self::assertInstanceOf(ImmutableFilterChain::class, $chain);
    }

    public function testAChainCanBeBuiltWithOptions(): void
    {
        $chain = $this->pluginManager->build(ImmutableFilterChain::class, [
            'callbacks' => [
                ['callback' => new StringToLower()],
            ],
        ]);

        self::assertSame('foo', $chain->filter('FOO'));
    }
}
