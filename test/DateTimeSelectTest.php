<?php

declare(strict_types=1);

namespace LaminasTest\Filter;

use Laminas\Filter\DateTimeSelect as DateTimeSelectFilter;
use Laminas\Filter\Exception\RuntimeException;
use PHPUnit\Framework\TestCase;

class DateTimeSelectTest extends TestCase
{
    /**
     * @dataProvider provideFilter
     */
    public function testFilter(array $options, array $input, ?string $expected): void
    {
        $sut = new DateTimeSelectFilter();
        $sut->setOptions($options);
        self::assertSame($expected, $sut->filter($input));
    }

    /** @return list<array{0: array, 1: array, 2: null|string}> */
    public function provideFilter(): array
    {
        return [
            [
                [],
                ['year' => '2014', 'month' => '10', 'day' => '26', 'hour' => '12', 'minute' => '35'],
                '2014-10-26 12:35:00',
            ],
            [
                ['nullOnEmpty' => true],
                ['year' => null, 'month' => '10', 'day' => '26', 'hour' => '12', 'minute' => '35'],
                null,
            ],
            [
                ['null_on_empty' => true],
                ['year' => null, 'month' => '10', 'day' => '26', 'hour' => '12', 'minute' => '35'],
                null,
            ],
            [
                ['nullOnAllEmpty' => true],
                ['year' => null, 'month' => null, 'day' => null, 'hour' => null, 'minute' => null],
                null,
            ],
            [
                ['null_on_all_empty' => true],
                ['year' => null, 'month' => null, 'day' => null, 'hour' => null, 'minute' => null],
                null,
            ],
        ];
    }

    public function testInvalidInput(): void
    {
        $this->expectException(RuntimeException::class);
        $sut = new DateTimeSelectFilter();
        $sut->filter(['year' => '2120', 'month' => '10', 'day' => '26', 'hour' => '12']);
    }
}
