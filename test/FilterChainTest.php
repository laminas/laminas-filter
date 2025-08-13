<?php

declare(strict_types=1);

namespace LaminasTest\Filter;

use Laminas\Filter\FilterChain;
use Laminas\Filter\FilterPluginManager;
use Laminas\Filter\PregReplace;
use Laminas\Filter\StringToLower;
use Laminas\Filter\StringTrim;
use Laminas\Filter\StripTags;
use Laminas\ServiceManager\ServiceManager;
use LaminasTest\Filter\TestAsset\StrRepeatFilterInterface;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;

use function count;
use function iterator_to_array;
use function strtolower;
use function strtoupper;
use function trim;

/**
 * @psalm-import-type FilterChainConfiguration from FilterChain
 * @psalm-import-type ServiceManagerConfiguration from ServiceManager
 */
final class FilterChainTest extends TestCase
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

    public function testEmptyFilterChainReturnsOriginalValue(): void
    {
        $chain = new FilterChain($this->plugins);
        $value = 'something';
        self::assertSame($value, $chain->filter($value));
    }

    public function testFiltersAreExecutedInFifoOrder(): void
    {
        $chain = new FilterChain($this->plugins);
        $chain->attach(new TestAsset\LowerCase())
            ->attach(new TestAsset\StripUpperCase());
        $value         = 'AbC';
        $valueExpected = 'abc';
        self::assertSame($valueExpected, $chain->filter($value));
    }

    public function testFiltersAreExecutedAccordingToPriority(): void
    {
        $chain = new FilterChain($this->plugins);
        $chain->attach(new TestAsset\StripUpperCase())
            ->attach(new TestAsset\LowerCase(), 100);
        $value         = 'AbC';
        $valueExpected = 'b';
        self::assertSame($valueExpected, $chain->filter($value));
    }

    public function testAllowsConnectingArbitraryCallbacks(): void
    {
        $chain = new FilterChain($this->plugins);
        $chain->attach(static fn(string $value): string => strtolower($value));
        $value = 'AbC';
        self::assertSame('abc', $chain->filter($value));
    }

    public function testAllowsConnectingViaClassShortName(): void
    {
        $chain = new FilterChain($this->plugins);
        $chain->attachByName(StringTrim::class, [], 100)
            ->attachByName(StripTags::class)
            ->attachByName(StringToLower::class, ['encoding' => 'utf-8'], 900);

        $value         = '<span id="foo"> ABC </span>';
        $valueExpected = 'abc';
        self::assertSame($valueExpected, $chain->filter($value));
    }

    public function testAllowsConfiguringFilters(): void
    {
        $config        = $this->getChainConfig();
        $chain         = new FilterChain($this->plugins, $config);
        $value         = '<a id="foo"> abc </a><img id="bar" />';
        $valueExpected = 'ABC <IMG ID="BAR" />ABC <IMG ID="BAR" />';
        self::assertSame($valueExpected, $chain->filter($value));
    }

    public function testAllowsConfiguringFiltersViaConstructor(): void
    {
        $config        = $this->getChainConfig();
        $chain         = new FilterChain($this->plugins, $config);
        $value         = '<span id="foo"> abc </span>';
        $valueExpected = 'ABCABC';
        self::assertSame($valueExpected, $chain->filter($value));
    }

    public function testCanRetrieveFilterWithUndefinedConstructor(): void
    {
        $chain    = new FilterChain($this->plugins, [
            'filters' => [
                ['name' => 'int'],
            ],
        ]);
        $filtered = $chain->filter('127.1');
        self::assertSame(127, $filtered);
    }

    /** @return FilterChainConfiguration */
    private function getChainConfig(): array
    {
        return [
            'callbacks' => [
                ['callback' => [self::class, 'staticUcaseFilter']],
                ['callback' => new StrRepeatFilterInterface()],
                [
                    'priority' => 10000,
                    'callback' => static fn(string $value): string => trim($value),
                ],
            ],
            'filters'   => [
                [
                    'name'     => StripTags::class,
                    'options'  => ['allowTags' => ['img'], 'allowAttribs' => ['id']],
                    'priority' => 10100,
                ],
            ],
        ];
    }

    public static function staticUcaseFilter(string $value): string
    {
        return strtoupper($value);
    }

    #[Group('Laminas-412')]
    public function testCanAttachMultipleFiltersOfTheSameTypeAsDiscreteInstances(): void
    {
        $chain = new FilterChain($this->plugins);
        $chain->attachByName(PregReplace::class, [
            'pattern'     => '/Foo/',
            'replacement' => 'Bar',
        ]);
        $chain->attachByName(PregReplace::class, [
            'pattern'     => '/Bar/',
            'replacement' => 'PARTY',
        ]);

        self::assertSame(2, count($chain));
        $filters = iterator_to_array($chain);
        $compare = null;
        foreach ($filters as $filter) {
            self::assertNotSame($compare, $filter);
            $compare = $filter;
        }

        self::assertSame('Tu et PARTY', $chain->filter('Tu et Foo'));
    }

    public function testClone(): void
    {
        $chain = new FilterChain($this->plugins);
        $clone = clone $chain;

        $chain->attachByName(StripTags::class);

        self::assertCount(0, $clone);
    }

    public function testMergingTwoFilterChainsKeepFiltersPriority(): void
    {
        $value         = 'AbC';
        $valueExpected = 'abc';

        $chain = new FilterChain($this->plugins);
        $chain->attach(new TestAsset\StripUpperCase())
            ->attach(new TestAsset\LowerCase(), 1001);
        self::assertSame($valueExpected, $chain->filter($value));

        $chain = new FilterChain($this->plugins);
        $chain->attach(new TestAsset\LowerCase(), 1001)
            ->attach(new TestAsset\StripUpperCase());
        self::assertSame($valueExpected, $chain->filter($value));

        $chain = new FilterChain($this->plugins);
        $chain->attach(new TestAsset\LowerCase(), 1001);
        $chainToMerge = new FilterChain($this->plugins);
        $chainToMerge->attach(new TestAsset\StripUpperCase());
        $chain->merge($chainToMerge);
        self::assertSame(2, $chain->count());
        self::assertSame($valueExpected, $chain->filter($value));

        $chain = new FilterChain($this->plugins);
        $chain->attach(new TestAsset\StripUpperCase());
        $chainToMerge = new FilterChain($this->plugins);
        $chainToMerge->attach(new TestAsset\LowerCase(), 1001);
        $chain->merge($chainToMerge);
        self::assertSame(2, $chain->count());
        self::assertSame($valueExpected, $chain->filter($value));
    }

    public function testThatIteratingOverAFilterChainDirectlyYieldsExpectedFilters(): void
    {
        $filter1 = new StringToLower();
        $filter2 = new StripTags();

        $chain = new FilterChain($this->plugins);
        $chain->attach($filter1, 10);
        $chain->attach($filter2, 20);

        $filters = iterator_to_array($chain);
        self::assertEquals([
            0 => $filter1,
            1 => $filter2,
        ], $filters);
    }

    public function testTheFilterChainIsInvokable(): void
    {
        $chain = new FilterChain($this->plugins);
        $chain->attach(new StringToLower());

        self::assertSame('foo', $chain->__invoke('FOO'));
    }

    public function testFilterChainSpecAcceptsFilterInstances(): void
    {
        $filter = new StringTrim();
        $chain  = new FilterChain($this->plugins, [
            'filters' => [
                $filter,
            ],
        ]);

        $filters = iterator_to_array($chain);
        self::assertSame([0 => $filter], $filters);
    }

    public function testServiceManagerServicesCanBeUsedInChains(): void
    {
        $closure = static fn (mixed $value): mixed => $value;
        $plugins = self::pluginManagerWithConfig([
            'services' => [
                'custom' => $closure,
            ],
        ]);

        $chain = iterator_to_array(new FilterChain($plugins, [
            'filters' => [
                ['name' => StringTrim::class],
                ['name' => 'custom'],
            ],
        ]), false);

        self::assertCount(2, $chain);
        self::assertSame($closure, $chain[1]);
    }
}
