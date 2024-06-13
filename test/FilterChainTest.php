<?php

declare(strict_types=1);

namespace LaminasTest\Filter;

use ArrayIterator;
use Laminas\Filter\FilterChain;
use Laminas\Filter\PregReplace;
use Laminas\Filter\StringToLower;
use Laminas\Filter\StringTrim;
use Laminas\Filter\StripTags;
use LaminasTest\Filter\TestAsset\StrRepeatFilterInterface;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;

use function count;
use function iterator_to_array;
use function serialize;
use function strtolower;
use function strtoupper;
use function trim;
use function unserialize;

/** @psalm-import-type FilterChainConfiguration from FilterChain */
class FilterChainTest extends TestCase
{
    public function testEmptyFilterChainReturnsOriginalValue(): void
    {
        $chain = new FilterChain();
        $value = 'something';
        self::assertSame($value, $chain->filter($value));
    }

    public function testFiltersAreExecutedInFifoOrder(): void
    {
        $chain = new FilterChain();
        $chain->attach(new TestAsset\LowerCase())
            ->attach(new TestAsset\StripUpperCase());
        $value         = 'AbC';
        $valueExpected = 'abc';
        self::assertSame($valueExpected, $chain->filter($value));
    }

    public function testFiltersAreExecutedAccordingToPriority(): void
    {
        $chain = new FilterChain();
        $chain->attach(new TestAsset\StripUpperCase())
            ->attach(new TestAsset\LowerCase(), 100);
        $value         = 'AbC';
        $valueExpected = 'b';
        self::assertSame($valueExpected, $chain->filter($value));
    }

    public function testAllowsConnectingArbitraryCallbacks(): void
    {
        $chain = new FilterChain();
        $chain->attach(static fn(string $value): string => strtolower($value));
        $value = 'AbC';
        self::assertSame('abc', $chain->filter($value));
    }

    public function testAllowsConnectingViaClassShortName(): void
    {
        $chain = new FilterChain();
        $chain->attachByName(StringTrim::class, [], 100)
            ->attachByName(StripTags::class)
            ->attachByName(StringToLower::class, ['encoding' => 'utf-8'], 900);

        $value         = '<a name="foo"> ABC </a>';
        $valueExpected = 'abc';
        self::assertSame($valueExpected, $chain->filter($value));
    }

    public function testAllowsConfiguringFilters(): void
    {
        $config = $this->getChainConfig();
        $chain  = new FilterChain();
        $chain->setOptions($config);
        $value         = '<a name="foo"> abc </a><img id="bar" />';
        $valueExpected = 'ABC <IMG ID="BAR" />ABC <IMG ID="BAR" />';
        self::assertSame($valueExpected, $chain->filter($value));
    }

    public function testAllowsConfiguringFiltersViaConstructor(): void
    {
        $config        = $this->getChainConfig();
        $chain         = new FilterChain($config);
        $value         = '<a name="foo"> abc </a>';
        $valueExpected = 'ABCABC';
        self::assertSame($valueExpected, $chain->filter($value));
    }

    public function testConfigurationAllowsTraversableObjects(): void
    {
        $config        = $this->getChainConfig();
        $config        = new ArrayIterator($config);
        $chain         = new FilterChain($config);
        $value         = '<a name="foo"> abc </a>';
        $valueExpected = 'ABCABC';
        self::assertSame($valueExpected, $chain->filter($value));
    }

    public function testCanRetrieveFilterWithUndefinedConstructor(): void
    {
        $chain    = new FilterChain([
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
                    'options'  => ['allowTags' => 'img', 'allowAttribs' => 'id'],
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
        $chain = new FilterChain();
        $chain->attachByName(PregReplace::class, [
            'pattern'     => '/Foo/',
            'replacement' => 'Bar',
        ]);
        $chain->attachByName(PregReplace::class, [
            'pattern'     => '/Bar/',
            'replacement' => 'PARTY',
        ]);

        self::assertSame(2, count($chain));
        $filters = $chain->getFilters();
        $compare = null;
        foreach ($filters as $filter) {
            self::assertNotSame($compare, $filter);
            $compare = $filter;
        }

        self::assertSame('Tu et PARTY', $chain->filter('Tu et Foo'));
    }

    public function testClone(): void
    {
        $chain = new FilterChain();
        $clone = clone $chain;

        $chain->attachByName(StripTags::class);

        self::assertCount(0, $clone);
    }

    public function testCanSerializeFilterChain(): void
    {
        $chain = new FilterChain();
        $chain->attach(new TestAsset\LowerCase())
            ->attach(new TestAsset\StripUpperCase());
        $serialized = serialize($chain);

        $unserialized = unserialize($serialized);
        self::assertInstanceOf(FilterChain::class, $unserialized);
        self::assertSame(2, count($unserialized));
        $value         = 'AbC';
        $valueExpected = 'abc';
        self::assertSame($valueExpected, $unserialized->filter($value));
    }

    public function testMergingTwoFilterChainsKeepFiltersPriority(): void
    {
        $value         = 'AbC';
        $valueExpected = 'abc';

        $chain = new FilterChain();
        $chain->attach(new TestAsset\StripUpperCase())
            ->attach(new TestAsset\LowerCase(), 1001);
        self::assertSame($valueExpected, $chain->filter($value));

        $chain = new FilterChain();
        $chain->attach(new TestAsset\LowerCase(), 1001)
            ->attach(new TestAsset\StripUpperCase());
        self::assertSame($valueExpected, $chain->filter($value));

        $chain = new FilterChain();
        $chain->attach(new TestAsset\LowerCase(), 1001);
        $chainToMerge = new FilterChain();
        $chainToMerge->attach(new TestAsset\StripUpperCase());
        $chain->merge($chainToMerge);
        self::assertSame(2, $chain->count());
        self::assertSame($valueExpected, $chain->filter($value));

        $chain = new FilterChain();
        $chain->attach(new TestAsset\StripUpperCase());
        $chainToMerge = new FilterChain();
        $chainToMerge->attach(new TestAsset\LowerCase(), 1001);
        $chain->merge($chainToMerge);
        self::assertSame(2, $chain->count());
        self::assertSame($valueExpected, $chain->filter($value));
    }

    public function testThatIteratingOverAFilterChainDirectlyYieldsExpectedFilters(): void
    {
        $filter1 = new StringToLower();
        $filter2 = new StripTags();

        $chain = new FilterChain();
        $chain->attach($filter1, 10);
        $chain->attach($filter2, 20);

        $filters = iterator_to_array($chain);
        self::assertEquals([
            0 => $filter1,
            1 => $filter2,
        ], $filters);
    }

    public function testThatIteratingOverGetFiltersYieldsExpectedFilters(): void
    {
        $filter1 = new StringToLower();
        $filter2 = new StripTags();

        $chain = new FilterChain();
        $chain->attach($filter1, 10);
        $chain->attach($filter2, 20);

        $filters = iterator_to_array($chain->getFilters());
        self::assertEquals([
            0 => $filter1,
            1 => $filter2,
        ], $filters);
    }
}
