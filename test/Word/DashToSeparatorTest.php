<?php

declare(strict_types=1);

namespace LaminasTest\Filter\Word;

use Laminas\Filter\Word\DashToSeparator as DashToSeparatorFilter;
use PHPUnit\Framework\TestCase;
use stdClass;

class DashToSeparatorTest extends TestCase
{
    public function testFilterSeparatesDashedWordsWithDefaultSpaces()
    {
        $string   = 'dash-separated-words';
        $filter   = new DashToSeparatorFilter();
        $filtered = $filter($string);

        $this->assertNotEquals($string, $filtered);
        $this->assertEquals('dash separated words', $filtered);
    }

    public function testFilterSeparatesDashedWordsWithSomeString()
    {
        $string   = 'dash-separated-words';
        $filter   = new DashToSeparatorFilter(':-:');
        $filtered = $filter($string);

        $this->assertNotEquals($string, $filtered);
        $this->assertEquals('dash:-:separated:-:words', $filtered);
    }

    /**
     * @return void
     */
    public function testFilterSupportArray()
    {
        $filter = new DashToSeparatorFilter();

        $input = [
            'dash-separated-words',
            'something-different',
        ];

        $filtered = $filter($input);

        $this->assertNotEquals($input, $filtered);
        $this->assertEquals(['dash separated words', 'something different'], $filtered);
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
     * @return void
     */
    public function testReturnUnfiltered($input)
    {
        $filter = new DashToSeparatorFilter();

        $this->assertEquals($input, $filter($input));
    }
}
