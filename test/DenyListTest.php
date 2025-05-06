<?php

declare(strict_types=1);

namespace LaminasTest\Filter;

use ArrayObject;
use Laminas\Filter\DenyList as DenyListFilter;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Throwable;
use TypeError;

use function gettype;
use function sprintf;
use function var_export;

final class DenyListTest extends TestCase
{
    public function testConstructorOptions(): void
    {
        $filter = new DenyListFilter([
            'list'   => ['test', 1],
            'strict' => true,
        ]);

        self::assertSame('1', $filter->filter('1'), 'Strict options infer that string 1 is not in the list');
        self::assertNull($filter->filter('test'));
        self::assertNull($filter->filter(1));
    }

    public function testConstructorDefaults(): void
    {
        $filter = new DenyListFilter();

        self::assertSame('test', $filter->filter('test'));
    }

    public function testWithPluginManager(): void
    {
        $pluginManager = CreatePluginManager::withDefaults();
        $filter        = $pluginManager->get('DenyList');

        self::assertInstanceOf(DenyListFilter::class, $filter);
    }

    public function testListOptionShouldBeIterable(): void
    {
        $this->expectException(Throwable::class);
        /** @psalm-suppress InvalidArgument */
        new DenyListFilter([
            'list' => 'foo',
        ]);
    }

    public function testTraversableConvertsToArray(): void
    {
        $filter = new DenyListFilter([
            'list' => new ArrayObject([1, 2, 'test']),
        ]);
        self::assertSame(null, $filter->filter('1'));
        self::assertSame(null, $filter->filter('test'));
    }

    public function testStrictOptionShouldBeBoolean(): void
    {
        $this->expectException(TypeError::class);
        /** @psalm-suppress InvalidArgument */
        new DenyListFilter([
            'strict' => 1,
        ]);
    }

    #[DataProvider('defaultTestProvider')]
    public function testWillReturnValueWhenNoListHasBeenProvided(mixed $value): void
    {
        $filter = new DenyListFilter();
        self::assertSame($value, $filter->filter($value));
    }

    #[DataProvider('defaultTestProvider')]
    public function testDefault(mixed $value, mixed $expected): void
    {
        $filter = new DenyListFilter();
        self::assertSame($expected, $filter->filter($value));
    }

    /**
     * @param array<array-key, mixed> $list
     * @param list<array{0: mixed, 1: mixed}> $testData
     */
    #[DataProvider('listTestProvider')]
    public function testList(bool $strict, array $list, array $testData): void
    {
        $filter = new DenyListFilter([
            'strict' => $strict,
            'list'   => $list,
        ]);
        foreach ($testData as $data) {
            /**
             * @var mixed $value
             * @var mixed $expected
             */
            [0 => $value, 1 => $expected] = $data;
            $message                      = sprintf(
                '%s (%s) is not filtered as %s; type = %s, strict = %b',
                var_export($value, true),
                gettype($value),
                var_export($expected, true),
                gettype($value),
                (int) $strict
            );
            self::assertSame($expected, $filter->filter($value), $message);
        }
    }

    /** @return list<array{0: mixed, 1: mixed}> */
    public static function defaultTestProvider(): array
    {
        return [
            ['test',   'test'],
            [0,        0],
            [0.1,      0.1],
            [[], []],
            [null,     null],
        ];
    }

    /** @return list<array{0: bool, 1: array<array-key, mixed>, 2: list<array{0:mixed, 1: mixed}>}> */
    public static function listTestProvider(): array
    {
        return [
            [
                true, //strict
                ['test', 0],
                [
                    ['test',   null],
                    [0,        null],
                    [null,     null],
                    [false,    false],
                    [0.0,      0.0],
                    [[], []],
                ],
            ],
            [
                false, //not strict
                ['test', 0],
                [
                    ['test',   null],
                    [0,        null],
                    [null,     null],
                    [false,    null],
                    [0.0,      null],
                    [0.1,      0.1],
                    [[], []],
                ],
            ],
        ];
    }
}
