<?php

declare(strict_types=1);

namespace LaminasTest\Filter;

use Laminas\Filter\AllowList as AllowListFilter;
use Laminas\Stdlib\ArrayObject;
use Laminas\Stdlib\Exception;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

use function gettype;
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

        self::assertSame(true, $filter->getStrict());
        self::assertSame(['test', 1], $filter->getList());
    }

    public function testConstructorDefaults(): void
    {
        $filter = new AllowListFilter();

        self::assertSame(false, $filter->getStrict());
        self::assertSame([], $filter->getList());
    }

    public function testWithPluginManager(): void
    {
        $pluginManager = CreatePluginManager::withDefaults();
        $filter        = $pluginManager->get('AllowList');

        self::assertInstanceOf(AllowListFilter::class, $filter);
    }

    public function testNullListShouldThrowException(): void
    {
        $this->expectException(Exception\InvalidArgumentException::class);
        new AllowListFilter([
            'list' => null,
        ]);
    }

    public function testTraversableConvertsToArray(): void
    {
        $array  = ['test', 1];
        $obj    = new ArrayObject(['test', 1]);
        $filter = new AllowListFilter([
            'list' => $obj,
        ]);
        self::assertSame($array, $filter->getList());
    }

    public function testSetStrictShouldCastToBoolean(): void
    {
        $filter = new AllowListFilter([
            'strict' => 1,
        ]);
        self::assertSame(true, $filter->getStrict());
    }

    #[DataProvider('defaultTestProvider')]
    public function testDefault(mixed $value): void
    {
        $filter = new AllowListFilter();
        self::assertNull($filter->filter($value));
    }

    #[DataProvider('listTestProvider')]
    public function testList(bool $strict, array $list, array $testData): void
    {
        $filter = new AllowListFilter([
            'strict' => $strict,
            'list'   => $list,
        ]);
        foreach ($testData as $data) {
            [$value, $expected] = $data;
            $message            = sprintf(
                '%s (%s) is not filtered as %s; type = %s',
                var_export($value, true),
                gettype($value),
                var_export($expected, true),
                $strict
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

    /** @return list<array{0: bool, 1: array, 2: array}> */
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
}
