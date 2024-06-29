<?php

declare(strict_types=1);

namespace LaminasTest\Filter;

use Laminas\Filter\AllowList as AllowListFilter;
use Laminas\Stdlib\ArrayObject;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Throwable;
use TypeError;

use function assert;
use function gettype;
use function is_array;
use function sprintf;
use function var_export;

class AllowListTest extends TestCase
{
    public function testConstructorOptions(): void
    {
        $filter = new AllowListFilter([
            'list'   => ['test', 1],
            'strict' => true,
        ]);

        self::assertNull($filter->filter('1'), 'Strict options infer that string 1 is not in the list');
        self::assertSame(1, $filter->filter(1));
        self::assertSame('test', $filter->filter('test'));
    }

    public function testConstructorDefaults(): void
    {
        $filter = new AllowListFilter();
        self::assertNull($filter->filter('anything'));
    }

    public function testWithPluginManager(): void
    {
        $pluginManager = CreatePluginManager::withDefaults();
        $filter        = $pluginManager->get('AllowList');

        self::assertInstanceOf(AllowListFilter::class, $filter);
    }

    public function testTraversableConvertsToArray(): void
    {
        $filter = new AllowListFilter([
            'list' => new ArrayObject(['test', 1]),
        ]);

        self::assertSame('1', $filter->filter('1'), 'The filter should be non-strict by default');
        self::assertSame(1, $filter->filter(1));
    }

    public function testSetStrictShouldBeBoolean(): void
    {
        $this->expectException(TypeError::class);
        /** @psalm-suppress InvalidArgument */
        new AllowListFilter([
            'strict' => 1,
        ]);
    }

    public function testListOptionShouldBeIterable(): void
    {
        /**
         * Throwable is expected because the actual exception will come from StdLib. In future, this might/should become
         * a TypeError
         */
        $this->expectException(Throwable::class);
        /** @psalm-suppress InvalidArgument */
        new AllowListFilter([
            'list' => 'foo',
        ]);
    }

    #[DataProvider('defaultTestProvider')]
    public function testFilterWillReturnNullForAnyValueWhenNoListHasBeenSupplied(mixed $value): void
    {
        $filter = new AllowListFilter(['list' => []]);
        self::assertNull($filter->filter($value));
    }

    /**
     * @param list<mixed> $list
     * @param array{0: mixed, 1: mixed} $testData
     */
    #[DataProvider('listTestProvider')]
    public function testList(bool $strict, array $list, array $testData): void
    {
        $filter = new AllowListFilter([
            'strict' => $strict,
            'list'   => $list,
        ]);
        foreach ($testData as $data) {
            assert(is_array($data));
            [$value, $expected] = $data;
            $message            = sprintf(
                '%s (%s) is not filtered as %s; type = %s',
                var_export($value, true),
                gettype($value),
                var_export($expected, true),
                $strict ? 'strict' : 'non-strict',
            );
            self::assertSame($expected, $filter->filter($value), $message);
        }
    }

    /** @return list<array{0: mixed, 1: null}> */
    public static function defaultTestProvider(): array
    {
        return [
            ['test',   null],
            [0,        null],
            [0.1,      null],
            [[], null],
            [null,     null],
        ];
    }

    /** @return list<array{0: bool, 1: list<mixed>, 2: list<array{0: mixed, 1: mixed}>}> */
    public static function listTestProvider(): array
    {
        return [
            [
                true, //strict
                ['test', 0],
                [
                    ['test',   'test'],
                    [0,        0],
                    [null,     null],
                    [false,    null],
                    [0.0,      null],
                    [[], null],
                ],
            ],
            [
                false, //not strict
                ['test', 0],
                [
                    ['test',   'test'],
                    [0,        0],
                    [null,     null],
                    [false,    false],
                    [0.0,      0.0],
                    [0.1,      null],
                    [[], null],
                ],
            ],
        ];
    }

    public function testFilterCanBeInvoked(): void
    {
        $filter = new AllowListFilter(['list' => ['foo']]);
        self::assertSame('foo', $filter->__invoke('foo'));
    }
}
