<?php

/**
 * @see       https://github.com/laminas/laminas-filter for the canonical source repository
 * @copyright https://github.com/laminas/laminas-filter/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-filter/blob/master/LICENSE.md New BSD License
 */

namespace LaminasTest\Filter\Word;

use Laminas\Filter\Word\SeparatorToSeparator as SeparatorToSeparatorFilter;

/**
 * Test class for Laminas\Filter\Word\SeparatorToSeparator.
 *
 * @group      Laminas_Filter
 */
class SeparatorToSeparatorTest extends \PHPUnit_Framework_TestCase
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

        $input = array(
            'dash separated words',
            '=test something'
        );
        $filtered = $filter($input);

        $this->assertNotEquals($input, $filtered);
        $this->assertEquals(array(
            'dash-separated-words',
            '=test-something'
        ), $filtered);
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
        return array(
            array(null),
            array(new \stdClass())
        );
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
