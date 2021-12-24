<?php

declare(strict_types=1);

namespace LaminasTest\Filter;

use Laminas\Filter\Exception\RuntimeException;
use Laminas\Filter\MonthSelect as MonthSelectFilter;
use PHPUnit\Framework\TestCase;

class MonthSelectTest extends TestCase
{
    /**
     * @dataProvider provideFilter
     * @param array $options filter options
     * @param array|mixed|null|string $input input provided to the filter
     * @param array|mixed|null|string $expected expected output
     */
    public function testFilter($options, $input, $expected): void
    {
        $sut = new MonthSelectFilter();
        $sut->setOptions($options);
        $this->assertSame($expected, $sut->filter($input));
    }

    public function provideFilter()
    {
        return [
            [[], ['year' => '2014', 'month' => '10'], '2014-10'],
            [['nullOnEmpty' => true], ['year' => null, 'month' => '10'], null],
            [['null_on_empty' => true], ['year' => null, 'month' => '10'], null],
            [['nullOnAllEmpty' => true], ['year' => null, 'month' => null], null],
            [['null_on_all_empty' => true], ['year' => null, 'month' => null], null],
        ];
    }

    public function testInvalidInput(): void
    {
        $this->expectException(RuntimeException::class);
        $sut = new MonthSelectFilter();
        $sut->filter(['year' => '2120']);
    }
}
