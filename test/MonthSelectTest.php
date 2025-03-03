<?php

declare(strict_types=1);

namespace LaminasTest\Filter;

use Laminas\Filter\MonthSelect as MonthSelectFilter;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/** @psalm-import-type Options from MonthSelectFilter */
final class MonthSelectTest extends TestCase
{
    /** @param Options $options */
    #[DataProvider('provideFilter')]
    public function testFilter(array $options, array $input, ?string $expected): void
    {
        $sut = new MonthSelectFilter($options);

        self::assertSame($expected, $sut->filter($input));
    }

    /** @return list<array{0: Options, 1: array, 2: string|null}> */
    public static function provideFilter(): array
    {
        return [
            [[], ['year' => '2014', 'month' => '2'], '2014-02'],
            [[], ['year' => '2014', 'month' => '10'], '2014-10'],
            [[], ['year' => 2014, 'month' => 10], '2014-10'],
            [['null_on_empty' => true], ['year' => null, 'month' => '10'], null],
            [['null_on_empty' => true], ['month' => null], null],
            [['null_on_empty' => true], ['year' => null], null],
            [['null_on_all_empty' => true], ['year' => null, 'month' => null], null],
            [['null_on_all_empty' => true], [], null],
            [['null_on_all_empty' => true], ['year' => '', 'month' => ''], null],
        ];
    }

    #[DataProvider('provideInvalidFilterValues')]
    public function testInvalidInput(mixed $value): void
    {
        $sut = new MonthSelectFilter();

        self::assertSame($value, $sut->filter($value));
    }

    /** @return array<string, array{0: mixed}> */
    public static function provideInvalidFilterValues(): array
    {
        return [
            'empty array'           => [[]],
            'missing year'          => [['month' => '10']],
            'missing month'         => [['year' => '2023']],
            'passed bool'           => [true],
            'passed string'         => ['string'],
            'passed int'            => [10],
            'passed float'          => [10.5],
            'invalid keys'          => [['should be year' => '2014', 'should be month' => '10']],
            'year is invalid type'  => [['year' => true, 'month' => '09']],
            'year out of bounds'    => [['year' => '-1', 'month' => '09']],
            'month is too high'     => [['year' => '2014', 'month' => '13']],
            'month is low'          => [['year' => '2014', 'month' => '0']],
            'month is invalid type' => [['year' => '2014', 'month' => true]],
            'invalid year'          => [['year' => 'not a year', 'month' => '10']],
            'invalid month'         => [['year' => '2023', 'month' => 'not a month']],
        ];
    }
}
