<?php

declare(strict_types=1);

namespace LaminasTest\Filter;

use Laminas\Filter\FilterPluginManager;
use Laminas\Filter\ImmutableFilterChain;
use Laminas\Filter\StringPrefix;
use Laminas\Filter\StringToLower;
use Laminas\Filter\StringTrim;
use Laminas\ServiceManager\ServiceManager;
use PHPUnit\Framework\TestCase;

use function implode;
use function str_replace;
use function str_split;
use function strrev;

/**
 * @psalm-import-type InstanceType from ImmutableFilterChain
 * @psalm-import-type ServiceManagerConfiguration from ServiceManager
 */
final class ImmutableFilterChainTest extends TestCase
{
    private FilterPluginManager $plugins;

    protected function setUp(): void
    {
        $this->plugins = self::pluginManagerWithConfig();
    }

    /** @param ServiceManagerConfiguration $config */
    private static function pluginManagerWithConfig(array $config = []): FilterPluginManager
    {
        return new FilterPluginManager(new ServiceManager(), $config);
    }

    public function testThatFiltersWillBeRetrievedFromThePluginManager(): void
    {
        $chain = ImmutableFilterChain::empty($this->plugins)
            ->attachByName(StringToLower::class);

        self::assertSame('foo', $chain->__invoke('Foo'));
    }

    public function testFiltersWillOperateInFifoOrderWhenPrioritiesAreEqual(): void
    {
        $makeFoo = static fn (): string => 'Foo';
        $makeBar = static fn (): string => 'Bar';

        $chain = ImmutableFilterChain::empty($this->plugins)
            ->attach($makeFoo, 10)
            ->attach($makeBar, 10);

        self::assertSame('Bar', $chain->filter('Baz'));
    }

    public function testFiltersWillOperateInPriorityOrder(): void
    {
        $makeFoo = static fn (): string => 'Foo';
        $makeBar = static fn (): string => 'Bar';

        $chain = ImmutableFilterChain::empty($this->plugins)
            ->attach($makeFoo, 10)
            ->attach($makeBar, 20);

        self::assertSame('Foo', $chain->filter('Baz'));

        $chain = ImmutableFilterChain::empty($this->plugins)
            ->attach($makeFoo, 20)
            ->attach($makeBar, 10);

        self::assertSame('Bar', $chain->filter('Baz'));
    }

    public function testTheFilterProducesTheSameResultForMultipleExecutions(): void
    {
        $makeFoo = static fn (): string => 'Foo';
        $makeBar = static fn (): string => 'Bar';

        $chain = ImmutableFilterChain::empty($this->plugins)
            ->attach($makeFoo)
            ->attach($makeBar);

        self::assertSame('Bar', $chain->filter('Baz'));
        self::assertSame('Bar', $chain->filter('Bat'));
    }

    public function testAnEmptyFilterChainWillReturnTheUnfilteredValue(): void
    {
        self::assertSame(
            'Foo',
            ImmutableFilterChain::empty($this->plugins)->filter('Foo'),
        );
    }

    public function testSpecificationWithCallables(): void
    {
        $spec = [
            'callbacks' => [
                [
                    'callback' => static fn (string $in): string => str_replace(' ', '_', $in),
                    'priority' => 10,
                ],
                [
                    'callback' => static fn (string $in): string => implode(' ', str_split($in)),
                ],
            ],
        ];

        $chain = ImmutableFilterChain::fromArray($spec, $this->plugins);

        self::assertSame('F_o_o', $chain->filter('Foo'));
    }

    public function testSpecificationWithFilters(): void
    {
        $spec = [
            'filters' => [
                [
                    'name'     => StringPrefix::class,
                    'options'  => [
                        'prefix' => 'Foo',
                    ],
                    'priority' => 10,
                ],
                [
                    'name' => StringToLower::class,
                ],
            ],
        ];

        $chain = ImmutableFilterChain::fromArray($spec, $this->plugins);

        self::assertSame('Foofoo', $chain->filter('Foo'));
    }

    public function testSpecificationWithFilterInstancesInCallbacks(): void
    {
        $spec = [
            'callbacks' => [
                [
                    'callback' => new StringPrefix([
                        'prefix' => 'Foo',
                    ]),
                    'priority' => 10,
                ],
                [
                    'callback' => new StringToLower(),
                ],
            ],
        ];

        $chain = ImmutableFilterChain::fromArray($spec, $this->plugins);

        self::assertSame('Foofoo', $chain->filter('Foo'));
    }

    public function testServiceManagerServicesCanBeUsedInChains(): void
    {
        $plugins = self::pluginManagerWithConfig([
            'services' => [
                'custom' => static fn (string $value): string => strrev($value),
            ],
        ]);

        $chain = ImmutableFilterChain::fromArray([
            'filters' => [
                ['name' => StringTrim::class],
                ['name' => 'custom'],
            ],
        ], $plugins);

        self::assertSame('oof', $chain->filter('  foo  '));
    }
}
