<?php

declare(strict_types=1);

namespace LaminasTest\Filter;

use Laminas\Filter\FilterChain;
use Laminas\Filter\FilterPluginManager;
use Laminas\Filter\FilterPluginManagerFactory;
use Laminas\Filter\StringToLower;
use Laminas\ServiceManager\ServiceManager;
use PHPUnit\Framework\TestCase;

final class FilterChainFactoryTest extends TestCase
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
        $chain = $this->pluginManager->get(FilterChain::class);

        self::assertInstanceOf(FilterChain::class, $chain);
    }

    public function testAChainCanBeBuiltWithOptions(): void
    {
        $chain = $this->pluginManager->build(FilterChain::class, [
            'callbacks' => [
                ['callback' => new StringToLower()],
            ],
        ]);

        self::assertSame('foo', $chain->filter('FOO'));
    }
}
