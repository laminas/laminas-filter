<?php

declare(strict_types=1);

namespace LaminasTest\Filter;

use Laminas\Filter\Exception;
use Laminas\Filter\ToNull as ToNullFilter;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

use function gettype;
use function sprintf;
use function var_export;

class ToNullTest extends TestCase
{
    public function testConstructorOptions(): void
    {
        $filter = new ToNullFilter([
            'type' => ToNullFilter::TYPE_INTEGER,
        ]);

        self::assertSame(ToNullFilter::TYPE_INTEGER, $filter->getType());
    }

    public function testConstructorParams(): void
    {
        $filter = new ToNullFilter(ToNullFilter::TYPE_INTEGER);

        self::assertSame(ToNullFilter::TYPE_INTEGER, $filter->getType());
    }

    #[DataProvider('defaultTestProvider')]
    public function testDefault(mixed $value, mixed $expected): void
    {
        $filter = new ToNullFilter();
        self::assertSame($expected, $filter->filter($value));
    }

    #[DataProvider('typeTestProvider')]
    public function testTypes(int $type, array $testData): void
    {
        $filter = new ToNullFilter($type);
        foreach ($testData as $data) {
            [$value, $expected] = $data;
            $message            = sprintf(
                '%s (%s) is not filtered as %s; type = %s',
                var_export($value, true),
                gettype($value),
                var_export($expected, true),
                $type
            );
            self::assertSame($expected, $filter->filter($value), $message);
        }
    }

    #[DataProvider('combinedTypeTestProvider')]
    public function testCombinedTypes(array $typeData, array $testData): void
    {
        foreach ($typeData as $type) {
            $filter = new ToNullFilter(['type' => $type]);
            foreach ($testData as $data) {
                [$value, $expected] = $data;
                $message            = sprintf(
                    '%s (%s) is not filtered as %s; type = %s',
                    var_export($value, true),
                    gettype($value),
                    var_export($expected, true),
                    var_export($type, true)
                );
                self::assertSame($expected, $filter->filter($value), $message);
            }
        }
    }

    public function testSettingFalseType(): void
    {
        $filter = new ToNullFilter();
        $this->expectException(Exception\InvalidArgumentException::class);
        $this->expectExceptionMessage('Unknown type value');
        $filter->setType(true);
    }

    public function testGettingDefaultType(): void
    {
        $filter = new ToNullFilter();
        self::assertSame(63, $filter->getType());
    }

    /**
     * Ensures that providing a duplicate initializing type results in the expected type
     */
    #[DataProvider('duplicateTypeProvider')]
    public function testDuplicateInitializationResultsInCorrectType(int|string $type, int $expected): void
    {
        $filter = new ToNullFilter([$type, $type]);
        self::assertSame($expected, $filter->getType());
    }

    /** @return list<array{0: int|string, 1: int}> */
    public static function duplicateTypeProvider(): array
    {
        return [
            [ToNullFilter::TYPE_BOOLEAN, ToNullFilter::TYPE_BOOLEAN],
            [ToNullFilter::TYPE_INTEGER, ToNullFilter::TYPE_INTEGER],
            [ToNullFilter::TYPE_EMPTY_ARRAY, ToNullFilter::TYPE_EMPTY_ARRAY],
            [ToNullFilter::TYPE_STRING, ToNullFilter::TYPE_STRING],
            [ToNullFilter::TYPE_ZERO_STRING, ToNullFilter::TYPE_ZERO_STRING],
            [ToNullFilter::TYPE_FLOAT, ToNullFilter::TYPE_FLOAT],
            [ToNullFilter::TYPE_ALL, ToNullFilter::TYPE_ALL],
            ['boolean', ToNullFilter::TYPE_BOOLEAN],
            ['integer', ToNullFilter::TYPE_INTEGER],
            ['array', ToNullFilter::TYPE_EMPTY_ARRAY],
            ['string', ToNullFilter::TYPE_STRING],
            ['zero', ToNullFilter::TYPE_ZERO_STRING],
            ['float', ToNullFilter::TYPE_FLOAT],
            ['all', ToNullFilter::TYPE_ALL],
        ];
    }

    /** @return list<array{0: mixed, 1: mixed}> */
    public static function defaultTestProvider(): array
    {
        return [
            [null, null],
            [false, null],
            [true, true],
            [0, null],
            [1, 1],
            [0.0, null],
            [1.0, 1.0],
            ['', null],
            ['abc', 'abc'],
            ['0', null],
            ['1', '1'],
            [[], null],
            [[0], [0]],
        ];
    }

    /** @return list<array{0: int, 1: array}> */
    public static function typeTestProvider(): array
    {
        return [
            [
                ToNullFilter::TYPE_BOOLEAN,
                [
                    [null, null],
                    [false, null],
                    [true, true],
                    [0, 0],
                    [1, 1],
                    [0.0, 0.0],
                    [1.0, 1.0],
                    ['', ''],
                    ['abc', 'abc'],
                    ['0', '0'],
                    ['1', '1'],
                    [[], []],
                    [[0], [0]],
                ],
            ],
            [
                ToNullFilter::TYPE_INTEGER,
                [
                    [null, null],
                    [false, false],
                    [true, true],
                    [0, null],
                    [1, 1],
                    [0.0, 0.0],
                    [1.0, 1.0],
                    ['', ''],
                    ['abc', 'abc'],
                    ['0', '0'],
                    ['1', '1'],
                    [[], []],
                    [[0], [0]],
                ],
            ],
            [
                ToNullFilter::TYPE_EMPTY_ARRAY,
                [
                    [null, null],
                    [false, false],
                    [true, true],
                    [0, 0],
                    [1, 1],
                    [0.0, 0.0],
                    [1.0, 1.0],
                    ['', ''],
                    ['abc', 'abc'],
                    ['0', '0'],
                    ['1', '1'],
                    [[], null],
                    [[0], [0]],
                ],
            ],
            [
                ToNullFilter::TYPE_STRING,
                [
                    [null, null],
                    [false, false],
                    [true, true],
                    [0, 0],
                    [1, 1],
                    [0.0, 0.0],
                    [1.0, 1.0],
                    ['', null],
                    ['abc', 'abc'],
                    ['0', '0'],
                    ['1', '1'],
                    [[], []],
                    [[0], [0]],
                ],
            ],
            [
                ToNullFilter::TYPE_ZERO_STRING,
                [
                    [null, null],
                    [false, false],
                    [true, true],
                    [0, 0],
                    [1, 1],
                    [0.0, 0.0],
                    [1.0, 1.0],
                    ['', ''],
                    ['abc', 'abc'],
                    ['0', null],
                    ['1', '1'],
                    [[], []],
                    [[0], [0]],
                ],
            ],
            [
                ToNullFilter::TYPE_FLOAT,
                [
                    [null, null],
                    [false, false],
                    [true, true],
                    [0, 0],
                    [1, 1],
                    [0.0, null],
                    [1.0, 1.0],
                    ['', ''],
                    ['abc', 'abc'],
                    ['0', '0'],
                    ['1', '1'],
                    [[], []],
                    [[0], [0]],
                ],
            ],
            [
                ToNullFilter::TYPE_ALL,
                [
                    [null, null],
                    [false, null],
                    [true, true],
                    [0, null],
                    [1, 1],
                    [0.0, null],
                    [1.0, 1.0],
                    ['', null],
                    ['abc', 'abc'],
                    ['0', null],
                    ['1', '1'],
                    [[], null],
                    [[0], [0]],
                ],
            ],
        ];
    }

    /** @return list<array[]> */
    public static function combinedTypeTestProvider(): array
    {
        return [
            [
                [
                    [
                        ToNullFilter::TYPE_ZERO_STRING,
                        ToNullFilter::TYPE_STRING,
                        ToNullFilter::TYPE_BOOLEAN,
                    ],
                    [
                        'zero',
                        'string',
                        'boolean',
                    ],
                    ToNullFilter::TYPE_ZERO_STRING | ToNullFilter::TYPE_STRING | ToNullFilter::TYPE_BOOLEAN,
                    ToNullFilter::TYPE_ZERO_STRING + ToNullFilter::TYPE_STRING + ToNullFilter::TYPE_BOOLEAN,
                ],
                [
                    [null, null],
                    [false, null],
                    [true, true],
                    [0, 0],
                    [1, 1],
                    [0.0, 0.0],
                    [1.0, 1.0],
                    ['', null],
                    ['abc', 'abc'],
                    ['0', null],
                    ['1', '1'],
                    [[], []],
                    [[0], [0]],
                ],
            ],
        ];
    }
}
