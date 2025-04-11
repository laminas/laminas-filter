<?php

declare(strict_types=1);

namespace LaminasTest\Filter;

use Laminas\Filter\DateTimeSelect as DateTimeSelectFilter;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/** @psalm-import-type Options from DateTimeSelectFilter */
final class DateTimeSelectTest extends TestCase
{
    /** @param Options $options */
    #[DataProvider('provideFilter')]
    public function testFilter(array $options, array $input, ?string $expected): void
    {
        $sut = new DateTimeSelectFilter($options);
        self::assertSame($expected, $sut->filter($input));
    }

    /** @return list<array{0: Options, 1: array, 2: null|string}> */
    public static function provideFilter(): array
    {
        return [
            [
                [],
                ['year' => '2014', 'month' => '10', 'day' => '26', 'hour' => '12', 'minute' => '35'],
                '2014-10-26 12:35:00',
            ],
            [
                [],
                ['year' => '2014', 'month' => '1', 'day' => '2', 'hour' => '3', 'minute' => '4', 'second' => '5'],
                '2014-01-02 03:04:05',
            ],
            [
                [],
                ['year' => 2014, 'month' => 1, 'day' => 2, 'hour' => 3, 'minute' => 4, 'second' => 5],
                '2014-01-02 03:04:05',
            ],
            [
                ['null_on_empty' => true],
                ['year' => null, 'month' => '10', 'day' => '26', 'hour' => '12', 'minute' => '35'],
                null,
            ],
            [
                ['null_on_empty' => true],
                ['year' => '2014', 'month' => null, 'day' => '26', 'hour' => '12', 'minute' => '35'],
                null,
            ],
            [
                ['null_on_empty' => true],
                ['year' => '2014', 'month' => '10', 'day' => null, 'hour' => '12', 'minute' => '35'],
                null,
            ],
            [
                ['null_on_empty' => true],
                ['year' => '2014', 'month' => '10', 'day' => '26', 'hour' => null, 'minute' => '35'],
                null,
            ],
            [
                ['null_on_empty' => true],
                ['year' => '2014', 'month' => '10', 'day' => '26', 'hour' => '12', 'minute' => null],
                null,
            ],
            [
                ['null_on_all_empty' => true],
                ['year' => null, 'month' => null, 'day' => null, 'hour' => null, 'minute' => null],
                null,
            ],
            [
                ['null_on_all_empty' => true],
                [],
                null,
            ],
            [
                ['null_on_all_empty' => true],
                ['year' => '', 'month' => '', 'day' => '', 'hour' => '', 'minute' => ''],
                null,
            ],
        ];
    }

    #[DataProvider('provideInvalidFilterValues')]
    public function testInvalidInput(mixed $value): void
    {
        $sut = new DateTimeSelectFilter();

        self::assertSame($value, $sut->filter($value));
    }

    /** @return array<string, array{0: mixed}> */
    public static function provideInvalidFilterValues(): array
    {
        return [
            'empty array'            => [[]],
            'missing year'           => [['month' => '10', 'day' => '26', 'hour' => '12', 'minute' => '35']],
            'missing month'          => [['year' => '2014', 'day' => '26', 'hour' => '12', 'minute' => '35']],
            'missing day'            => [['year' => '2014', 'month' => '10', 'hour' => '12', 'minute' => '35']],
            'missing hour'           => [['year' => '2014', 'month' => '10', 'day' => '26', 'minute' => '35']],
            'missing minute'         => [['year' => '2014', 'month' => '10', 'day' => '26', 'hour' => '12']],
            'passed bool'            => [true],
            'passed string'          => ['string'],
            'passed int'             => [10],
            'passed float'           => [10.5],
            'invalid keys'           => [
                ['not year' => '2014', 'not month' => '10', 'not day' => '2', 'not hour' => '2', 'not minute' => '2'],
            ],
            'year is invalid type'   => [
                ['year' => true, 'month' => '10', 'day' => '26', 'hour' => '12', 'minute' => '35'],
            ],
            'invalid year'           => [
                ['year' => 'not a year', 'month' => '10', 'day' => '2', 'hour' => '12', 'minute' => '35'],
            ],
            'year is float'          => [
                ['year' => '1.5', 'month' => '10', 'day' => '26', 'hour' => '12', 'minute' => '35'],
            ],
            'year out of bounds'     => [
                ['year' => '-1', 'month' => '10', 'day' => '26', 'hour' => '12', 'minute' => '35'],
            ],
            'invalid month'          => [
                ['year' => '2023', 'month' => 'not a month', 'day' => '2', 'hour' => '12', 'minute' => '35'],
            ],
            'month is too high'      => [
                ['year' => '2014', 'month' => '13', 'day' => '2', 'hour' => '12', 'minute' => '35'],
            ],
            'month is low'           => [
                ['year' => '2014', 'month' => '0', 'day' => '2', 'hour' => '12', 'minute' => '35'],
            ],
            'month is invalid type'  => [
                ['year' => '2014', 'month' => true, 'day' => '2', 'hour' => '12', 'minute' => '35'],
            ],
            'invalid day'            => [
                ['year' => '2023', 'month' => '10', 'day' => 'not a day', 'hour' => '12', 'minute' => '35'],
            ],
            'day is too high'        => [
                ['year' => '2014', 'month' => '2', 'day' => '30', 'hour' => '12', 'minute' => '35'],
            ],
            'day is low'             => [
                ['year' => '2014', 'month' => '0', 'day' => '2', 'hour' => '12', 'minute' => '35'],
            ],
            'day is invalid type'    => [
                ['year' => '2014', 'month' => '09', 'day' => true, 'hour' => '12', 'minute' => '35'],
            ],
            'invalid hour'           => [
                ['year' => '2023', 'month' => '10', 'day' => '26', 'hour' => 'not an hour', 'minute' => '35'],
            ],
            'hour is too high'       => [
                ['year' => '2014', 'month' => '10', 'day' => '26', 'hour' => '24', 'minute' => '35'],
            ],
            'hour is low'            => [
                ['year' => '2014', 'month' => '10', 'day' => '26', 'hour' => '-1', 'minute' => '35'],
            ],
            'hour is invalid type'   => [
                ['year' => '2014', 'month' => '10', 'day' => '26', 'hour' => true, 'minute' => '35'],
            ],
            'invalid minute'         => [
                ['year' => '2023', 'month' => '10', 'day' => '26', 'hour' => '12', 'minute' => 'not a minute'],
            ],
            'minute is too high'     => [
                ['year' => '2014', 'month' => '10', 'day' => '26', 'hour' => '12', 'minute' => '60'],
            ],
            'minute is low'          => [
                ['year' => '2014', 'month' => '10', 'day' => '26', 'hour' => '12', 'minute' => '-1'],
            ],
            'minute is invalid type' => [
                ['year' => '2014', 'month' => '10', 'day' => '26', 'hour' => '12', 'minute' => true],
            ],
            'invalid second'         => [
                [
                    'year'   => '2023',
                    'month'  => '10',
                    'day'    => '26',
                    'hour'   => '12',
                    'minute' => '35',
                    'second' => 'not a second',
                ],
            ],
            'second is too high'     => [
                ['year' => '2014', 'month' => '10', 'day' => '26', 'hour' => '12', 'minute' => '35', 'second' => '60'],
            ],
            'second is low'          => [
                ['year' => '2014', 'month' => '10', 'day' => '26', 'hour' => '12', 'minute' => '35', 'second' => '-1'],
            ],
            'second is invalid type' => [
                ['year' => '2014', 'month' => '10', 'day' => '26', 'hour' => '12', 'minute' => '35', 'second' => true],
            ],
        ];
    }
}
