<?php

declare(strict_types=1);

namespace LaminasTest\Filter;

use Laminas\Filter\DateSelect as DateSelectFilter;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/** @psalm-import-type Options from DateSelectFilter */
final class DateSelectTest extends TestCase
{
    /** @param Options $options */
    #[DataProvider('provideFilter')]
    public function testFilter(array $options, array $input, ?string $expected): void
    {
        $sut = new DateSelectFilter($options);

        self::assertSame($expected, $sut->filter($input));
    }

    /** @return list<array{0: array, 1: array, 2: string|null}> */
    public static function provideFilter(): array
    {
        return [
            [[], ['year' => '2014', 'month' => '2', 'day' => '7'], '2014-02-07'],
            [[], ['year' => '2014', 'month' => '10', 'day' => '26'], '2014-10-26'],
            [[], ['year' => 2014, 'month' => 10, 'day' => 26], '2014-10-26'],
            [['null_on_empty' => true], ['year' => null, 'month' => '10', 'day' => '26'], null],
            [['null_on_empty' => true], ['month' => null], null],
            [['null_on_empty' => true], ['year' => null], null],
            [['null_on_empty' => true], ['day' => null], null],
            [['null_on_all_empty' => true], ['year' => null, 'month' => null, 'day' => null], null],
            [['null_on_all_empty' => true], [], null],
            [['null_on_all_empty' => true], ['year' => '', 'month' => '', 'day' => ''], null],
        ];
    }

    #[DataProvider('provideInvalidFilterValues')]
    public function testInvalidInput(mixed $value): void
    {
        $sut = new DateSelectFilter();

        self::assertSame($value, $sut->filter($value));
    }

    /** @return array<string, array{0: mixed}> */
    public static function provideInvalidFilterValues(): array
    {
        return [
            'empty array'           => [[]],
            'missing year'          => [['day' => '2', 'month' => '10']],
            'missing month'         => [['day' => '2', 'year' => '2023']],
            'missing day'           => [['month' => '10', 'year' => '2023']],
            'passed bool'           => [true],
            'passed string'         => ['string'],
            'passed int'            => [10],
            'passed float'          => [10.5],
            'invalid keys'          => [['not year' => '2014', 'not month' => '10', 'not day' => '2']],
            'year is invalid type'  => [['year' => true, 'month' => '09', 'day' => '2']],
            'year is float'         => [['year' => '1.5', 'month' => '09', 'day' => '2']],
            'year out of bounds'    => [['year' => '-1', 'month' => '09', 'day' => '2']],
            'month is too high'     => [['year' => '2014', 'month' => '13', 'day' => '2']],
            'month is low'          => [['year' => '2014', 'month' => '0', 'day' => '2']],
            'month is invalid type' => [['year' => '2014', 'month' => true, 'day' => '2']],
            'day is too high'       => [['year' => '2014', 'month' => '2', 'day' => '30']],
            'day is low'            => [['year' => '2014', 'month' => '0', 'day' => '2']],
            'day is invalid type'   => [['year' => '2014', 'month' => '09', 'day' => true]],
            'invalid year'          => [['year' => 'not a year', 'month' => '10', 'day' => '2']],
            'invalid month'         => [['year' => '2023', 'month' => 'not a month', 'day' => '2']],
            'invalid day'           => [['year' => '2023', 'month' => '10', 'day' => 'not a day']],
        ];
    }
}
