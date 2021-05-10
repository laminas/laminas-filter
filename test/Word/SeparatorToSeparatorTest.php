<?php

namespace LaminasTest\Filter\Word;

use Laminas\Filter\Word\SeparatorToSeparator as SeparatorToSeparatorFilter;
use PHPUnit\Framework\TestCase;

class SeparatorToSeparatorTest extends TestCase
{
    public function testFilterSeparatesWordsByDefault()
    {
        $string   = 'dash separated words';
        $filter   = new SeparatorToSeparatorFilter();
        $filtered = $filter($string);

        $this->assertNotEquals($string, $filtered);
        $this->assertEquals('dash-separated-words', $filtered);
    }

    public function testFilterSupportArray()
    {
        $filter   = new SeparatorToSeparatorFilter();

        $input = [
            'dash separated words',
            '=test something'
        ];
        $filtered = $filter($input);

        $this->assertNotEquals($input, $filtered);
        $this->assertEquals([
            'dash-separated-words',
            '=test-something'
        ], $filtered);
    }

    public function testFilterSeparatesWordsWithSearchSpecified()
    {
        $string   = 'dash=separated=words';
        $filter   = new SeparatorToSeparatorFilter('=');
        $filtered = $filter($string);

        $this->assertNotEquals($string, $filtered);
        $this->assertEquals('dash-separated-words', $filtered);
    }

    public function testFilterSeparatesWordsWithSearchAndReplacementSpecified()
    {
        $string   = 'dash=separated=words';
        $filter   = new SeparatorToSeparatorFilter('=', '?');
        $filtered = $filter($string);

        $this->assertNotEquals($string, $filtered);
        $this->assertEquals('dash?separated?words', $filtered);
    }

    public function returnUnfilteredDataProvider()
    {
        return [
            [null],
            [new \stdClass()]
        ];
    }

    /**
     * @dataProvider returnUnfilteredDataProvider
     * @return void
     */
    public function testReturnUnfiltered($input)
    {
        $filter = new SeparatorToSeparatorFilter('=', '?');

        $this->assertEquals($input, $filter($input));
    }
}
