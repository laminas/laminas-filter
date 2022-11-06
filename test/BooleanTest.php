<?php

declare(strict_types=1);

namespace LaminasTest\Filter;

use Laminas\Filter\Boolean as BooleanFilter;
use Laminas\Filter\Exception;
use PHPUnit\Framework\TestCase;

use function gettype;
use function sprintf;
use function var_export;

class BooleanTest extends TestCase
{
    public function testConstructorOptions(): void
    {
        $filter = new BooleanFilter([
            'type'    => BooleanFilter::TYPE_INTEGER,
            'casting' => false,
        ]);

        self::assertSame(BooleanFilter::TYPE_INTEGER, $filter->getType());
        self::assertFalse($filter->getCasting());
    }

    public function testConstructorParams(): void
    {
        $filter = new BooleanFilter(BooleanFilter::TYPE_INTEGER, false);

        self::assertSame(BooleanFilter::TYPE_INTEGER, $filter->getType());
        self::assertFalse($filter->getCasting());
    }

    /**
     * @dataProvider defaultTestProvider
     */
    public function testDefault(mixed $value, bool $expected): void
    {
        $filter = new BooleanFilter();
        self::assertSame($expected, $filter->filter($value));
    }

    /**
     * @dataProvider noCastingTestProvider
     */
    public function testNoCasting(mixed $value, mixed $expected): void
    {
        $filter = new BooleanFilter('all', false);
        self::assertSame($expected, $filter->filter($value));
    }

    /**
     * @param array{0: mixed, 1: mixed} $testData
     * @dataProvider typeTestProvider
     */
    public function testTypes(int $type, array $testData): void
    {
        $filter = new BooleanFilter($type);
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

    /**
     * @param array $typeData
     * @param array $testData
     * @dataProvider combinedTypeTestProvider
     */
    public function testCombinedTypes($typeData, $testData): void
    {
        foreach ($typeData as $type) {
            $filter = new BooleanFilter(['type' => $type]);
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

    public function testLocalized(): void
    {
        $filter = new BooleanFilter([
            'type'         => BooleanFilter::TYPE_LOCALIZED,
            'translations' => [
                'yes' => true,
                'y'   => true,
                'no'  => false,
                'n'   => false,
                'yay' => true,
                'nay' => false,
            ],
        ]);

        self::assertTrue($filter->filter('yes'));
        self::assertTrue($filter->filter('yay'));
        self::assertFalse($filter->filter('n'));
        self::assertFalse($filter->filter('nay'));
    }

    public function testSettingFalseType(): void
    {
        $filter = new BooleanFilter();
        $this->expectException(Exception\InvalidArgumentException::class);
        $this->expectExceptionMessage('Unknown type value');
        $filter->setType(true);
    }

    public function testGettingDefaultType(): void
    {
        $filter = new BooleanFilter();
        self::assertSame(127, $filter->getType());
    }

    /**
     * Ensures that if a type is specified more than once, we get the expected type, not something else.
     * https://github.com/zendframework/zend-filter/issues/48
     *
     * @param mixed $type Type to double initialize
     * @dataProvider duplicateProvider
     */
    public function testDuplicateTypesWorkProperly(int|string $type, int $expected): void
    {
        $filter = new BooleanFilter([$type, $type]);
        self::assertSame($expected, $filter->getType());
    }

    /** @return list<array{0: mixed, 1: bool}> */
    public static function defaultTestProvider(): array
    {
        return [
            [false, false],
            [true, true],
            [0, false],
            [1, true],
            [0.0, false],
            [1.0, true],
            ['', false],
            ['abc', true],
            ['0', false],
            ['1', true],
            [[], false],
            [[0], true],
            [null, false],
            ['false', true],
            ['true', true],
            ['no', true],
            ['yes', true],
        ];
    }

    /** @return list<array{0: mixed, 1: mixed}> */
    public static function noCastingTestProvider(): array
    {
        return [
            [false, false],
            [true, true],
            [0, false],
            [1, true],
            [2, 2],
            [0.0, false],
            [1.0, true],
            [0.5, 0.5],
            ['', false],
            ['abc', 'abc'],
            ['0', false],
            ['1', true],
            ['2', '2'],
            [[], false],
            [[0], [0]],
            [null, false],
            ['false', false],
            ['true', true],
        ];
    }

    /** @return list<array{0: int, 1: mixed[]}> */
    public static function typeTestProvider(): array
    {
        return [
            [
                BooleanFilter::TYPE_BOOLEAN,
                [
                    [false, false],
                    [true, true],
                    [0, true],
                    [1, true],
                    [0.0, true],
                    [1.0, true],
                    ['', true],
                    ['abc', true],
                    ['0', true],
                    ['1', true],
                    [[], true],
                    [[0], true],
                    [null, true],
                    ['false', true],
                    ['true', true],
                    ['no', true],
                    ['yes', true],
                ],
            ],
            [
                BooleanFilter::TYPE_INTEGER,
                [
                    [false, true],
                    [true, true],
                    [0, false],
                    [1, true],
                    [0.0, true],
                    [1.0, true],
                    ['', true],
                    ['abc', true],
                    ['0', true],
                    ['1', true],
                    [[], true],
                    [[0], true],
                    [null, true],
                    ['false', true],
                    ['true', true],
                    ['no', true],
                    ['yes', true],
                ],
            ],
            [
                BooleanFilter::TYPE_FLOAT,
                [
                    [false, true],
                    [true, true],
                    [0, true],
                    [1, true],
                    [0.0, false],
                    [1.0, true],
                    ['', true],
                    ['abc', true],
                    ['0', true],
                    ['1', true],
                    [[], true],
                    [[0], true],
                    [null, true],
                    ['false', true],
                    ['true', true],
                    ['no', true],
                    ['yes', true],
                ],
            ],
            [
                BooleanFilter::TYPE_STRING,
                [
                    [false, true],
                    [true, true],
                    [0, true],
                    [1, true],
                    [0.0, true],
                    [1.0, true],
                    ['', false],
                    ['abc', true],
                    ['0', true],
                    ['1', true],
                    [[], true],
                    [[0], true],
                    [null, true],
                    ['false', true],
                    ['true', true],
                    ['no', true],
                    ['yes', true],
                ],
            ],
            [
                BooleanFilter::TYPE_ZERO_STRING,
                [
                    [false, true],
                    [true, true],
                    [0, true],
                    [1, true],
                    [0.0, true],
                    [1.0, true],
                    ['', true],
                    ['abc', true],
                    ['0', false],
                    ['1', true],
                    [[], true],
                    [[0], true],
                    [null, true],
                    ['false', true],
                    ['true', true],
                    ['no', true],
                    ['yes', true],
                ],
            ],
            [
                BooleanFilter::TYPE_EMPTY_ARRAY,
                [
                    [false, true],
                    [true, true],
                    [0, true],
                    [1, true],
                    [0.0, true],
                    [1.0, true],
                    ['', true],
                    ['abc', true],
                    ['0', true],
                    ['1', true],
                    [[], false],
                    [[0], true],
                    [null, true],
                    ['false', true],
                    ['true', true],
                    ['no', true],
                    ['yes', true],
                ],
            ],
            [
                BooleanFilter::TYPE_NULL,
                [
                    [false, true],
                    [true, true],
                    [0, true],
                    [1, true],
                    [0.0, true],
                    [1.0, true],
                    ['', true],
                    ['abc', true],
                    ['0', true],
                    ['1', true],
                    [[], true],
                    [[0], true],
                    [null, false],
                    ['false', true],
                    ['true', true],
                    ['no', true],
                    ['yes', true],
                ],
            ],
            [
                BooleanFilter::TYPE_PHP,
                [
                    [false, false],
                    [true, true],
                    [0, false],
                    [1, true],
                    [0.0, false],
                    [1.0, true],
                    ['', false],
                    ['abc', true],
                    ['0', false],
                    ['1', true],
                    [[], false],
                    [[0], true],
                    [null, false],
                    ['false', true],
                    ['true', true],
                    ['no', true],
                    ['yes', true],
                ],
            ],
            [
                BooleanFilter::TYPE_FALSE_STRING,
                [
                    [false, true],
                    [true, true],
                    [0, true],
                    [1, true],
                    [0.0, true],
                    [1.0, true],
                    ['', true],
                    ['abc', true],
                    ['0', true],
                    ['1', true],
                    [[], true],
                    [[0], true],
                    [null, true],
                    ['false', false],
                    ['true', true],
                    ['no', true],
                    ['yes', true],
                ],
            ],
            // default behaviour with no translations provided
            // all values filtered as true
            [
                BooleanFilter::TYPE_LOCALIZED,
                [
                    [false, true],
                    [true, true],
                    [0, true],
                    [1, true],
                    [0.0, true],
                    [1.0, true],
                    ['', true],
                    ['abc', true],
                    ['0', true],
                    ['1', true],
                    [[], true],
                    [[0], true],
                    [null, true],
                    ['false', true],
                    ['true', true],
                    ['no', true],
                    ['yes', true],
                ],
            ],
            [
                BooleanFilter::TYPE_ALL,
                [
                    [false, false],
                    [true, true],
                    [0, false],
                    [1, true],
                    [0.0, false],
                    [1.0, true],
                    ['', false],
                    ['abc', true],
                    ['0', false],
                    ['1', true],
                    [[], false],
                    [[0], true],
                    [null, false],
                    ['false', false],
                    ['true', true],
                    ['no', true],
                    ['yes', true],
                ],
            ],
        ];
    }

    public static function combinedTypeTestProvider(): array
    {
        return [
            [
                [
                    [
                        BooleanFilter::TYPE_ZERO_STRING,
                        BooleanFilter::TYPE_STRING,
                        BooleanFilter::TYPE_BOOLEAN,
                    ],
                    [
                        'zero',
                        'string',
                        'boolean',
                    ],
                    BooleanFilter::TYPE_ZERO_STRING | BooleanFilter::TYPE_STRING | BooleanFilter::TYPE_BOOLEAN,
                    BooleanFilter::TYPE_ZERO_STRING + BooleanFilter::TYPE_STRING + BooleanFilter::TYPE_BOOLEAN,
                ],
                [
                    [false, false],
                    [true, true],
                    [0, true],
                    [1, true],
                    [0.0, true],
                    [1.0, true],
                    ['', false],
                    ['abc', true],
                    ['0', false],
                    ['1', true],
                    [[], true],
                    [[0], true],
                    [null, true],
                    ['false', true],
                    ['true', true],
                    ['no', true],
                    ['yes', true],
                ],
            ],
        ];
    }

    /** @return list<array{0: int|string, 1: int}> */
    public static function duplicateProvider(): array
    {
        return [
            [BooleanFilter::TYPE_BOOLEAN, BooleanFilter::TYPE_BOOLEAN],
            [BooleanFilter::TYPE_INTEGER, BooleanFilter::TYPE_INTEGER],
            [BooleanFilter::TYPE_FLOAT, BooleanFilter::TYPE_FLOAT],
            [BooleanFilter::TYPE_STRING, BooleanFilter::TYPE_STRING],
            [BooleanFilter::TYPE_ZERO_STRING, BooleanFilter::TYPE_ZERO_STRING],
            [BooleanFilter::TYPE_EMPTY_ARRAY, BooleanFilter::TYPE_EMPTY_ARRAY],
            [BooleanFilter::TYPE_NULL, BooleanFilter::TYPE_NULL],
            [BooleanFilter::TYPE_PHP, BooleanFilter::TYPE_PHP],
            [BooleanFilter::TYPE_FALSE_STRING, BooleanFilter::TYPE_FALSE_STRING],
            [BooleanFilter::TYPE_LOCALIZED, BooleanFilter::TYPE_LOCALIZED],
            [BooleanFilter::TYPE_ALL, BooleanFilter::TYPE_ALL],
            ['boolean', BooleanFilter::TYPE_BOOLEAN],
            ['integer', BooleanFilter::TYPE_INTEGER],
            ['float', BooleanFilter::TYPE_FLOAT],
            ['string', BooleanFilter::TYPE_STRING],
            ['zero', BooleanFilter::TYPE_ZERO_STRING],
            ['array', BooleanFilter::TYPE_EMPTY_ARRAY],
            ['null', BooleanFilter::TYPE_NULL],
            ['php', BooleanFilter::TYPE_PHP],
            ['false', BooleanFilter::TYPE_FALSE_STRING],
            ['localized', BooleanFilter::TYPE_LOCALIZED],
            ['all', BooleanFilter::TYPE_ALL],
        ];
    }
}
