<?php

declare(strict_types=1);

namespace LaminasTest\Filter;

use Laminas\Filter\DateSelect as DateSelectFilter;
use Laminas\Filter\Exception\RuntimeException;
use PHPUnit\Framework\TestCase;

class DateSelectTest extends TestCase
{
    /**
     * @dataProvider provideFilter
     * @param array $options filter options
     * @param array|mixed|null|string $input input provided to the filter
     * @param array|mixed|null|string $expected expected output
     */
    public function testFilter($options, $input, $expected): void
    {
        $sut = new DateSelectFilter();
        $sut->setOptions($options);
        $this->assertSame($expected, $sut->filter($input));
    }

    public function provideFilter()
    {
        return [
            [[], ['year' => '2014', 'month' => '10', 'day' => '26'], '2014-10-26'],
            [['nullOnEmpty' => true], ['year' => null, 'month' => '10', 'day' => '26'], null],
            [['null_on_empty' => true], ['year' => null, 'month' => '10', 'day' => '26'], null],
            [['nullOnAllEmpty' => true], ['year' => null, 'month' => null, 'day' => null], null],
            [['null_on_all_empty' => true], ['year' => null, 'month' => null, 'day' => null], null],
        ];
    }

    public function testInvalidInput(): void
    {
        $this->expectException(RuntimeException::class);
        $sut = new DateSelectFilter();
        $sut->filter(['year' => '2120', 'month' => '07']);
    }
}
