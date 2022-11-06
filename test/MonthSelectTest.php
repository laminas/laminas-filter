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
     */
    public function testFilter(array $options, array $input, ?string $expected): void
    {
        $sut = new MonthSelectFilter();
        $sut->setOptions($options);
        self::assertSame($expected, $sut->filter($input));
    }

    /** @return list<array{0: array, 1: array, 2: string|null}> */
    public function provideFilter(): array
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
