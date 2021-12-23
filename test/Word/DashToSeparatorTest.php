<?php

declare(strict_types=1);

namespace LaminasTest\Filter\Word;

use Laminas\Filter\Word\DashToSeparator as DashToSeparatorFilter;
use PHPUnit\Framework\TestCase;
use stdClass;

class DashToSeparatorTest extends TestCase
{
    public function testFilterSeparatesDashedWordsWithDefaultSpaces(): void
    {
        $string   = 'dash-separated-words';
        $filter   = new DashToSeparatorFilter();
        $filtered = $filter($string);

        $this->assertNotEquals($string, $filtered);
        $this->assertSame('dash separated words', $filtered);
    }

    public function testFilterSeparatesDashedWordsWithSomeString(): void
    {
        $string   = 'dash-separated-words';
        $filter   = new DashToSeparatorFilter(':-:');
        $filtered = $filter($string);

        $this->assertNotEquals($string, $filtered);
        $this->assertSame('dash:-:separated:-:words', $filtered);
    }

    public function testFilterSupportArray(): void
    {
        $filter = new DashToSeparatorFilter();

        $input = [
            'dash-separated-words',
            'something-different',
        ];

        $filtered = $filter($input);

        $this->assertNotEquals($input, $filtered);
        $this->assertSame(['dash separated words', 'something different'], $filtered);
    }

    public function returnUnfilteredDataProvider()
    {
        return [
            [null],
            [new stdClass()],
        ];
    }

    /**
     * @dataProvider returnUnfilteredDataProvider
     */
    public function testReturnUnfiltered($input): void
    {
        $filter = new DashToSeparatorFilter();

        $this->assertSame($input, $filter($input));
    }

    /**
     * @return array<int|float|bool>[]
     */
    public function returnNonStringScalarValues(): array
    {
        return [
            [1],
            [1.0],
            [true],
            [false],
        ];
    }

    /**
     * @dataProvider returnNonStringScalarValues
     * @param int|float|bool $input
     */
    public function testShouldFilterNonStringScalarValues($input): void
    {
        $filter = new DashToSeparatorFilter();

        $this->assertSame((string) $input, $filter($input));
    }
}
