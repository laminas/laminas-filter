<?php

/**
 * @see       https://github.com/laminas/laminas-filter for the canonical source repository
 * @copyright https://github.com/laminas/laminas-filter/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-filter/blob/master/LICENSE.md New BSD License
 */

namespace LaminasTest\Filter;

use Laminas\Filter\DateTimeSelect as DateTimeSelectFilter;

class DateTimeSelectTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider provideFilter
     * @param $options
     * @param $input
     * @param $expected
     */
    public function testFilter($options, $input, $expected)
    {
        $sut = new DateTimeSelectFilter();
        $sut->setOptions($options);
        $this->assertEquals($expected, $sut->filter($input));
    }

    public function provideFilter()
    {
        return array(
            array(
                array(),
                array('year' => '2014', 'month' => '10', 'day' => '26', 'hour' => '12', 'minute' => '35'),
                '2014-10-26 12:35:00'
            ),
            array(
                array('nullOnEmpty' => true),
                array('year' => null, 'month' => '10', 'day' => '26', 'hour' => '12', 'minute' => '35'),
                null
            ),
            array(
                array('null_on_empty' => true),
                array('year' => null, 'month' => '10', 'day' => '26', 'hour' => '12', 'minute' => '35'),
                null
            ),
            array(
                array('nullOnAllEmpty' => true),
                array('year' => null, 'month' => null, 'day' => null, 'hour' => null, 'minute' => null),
                null
            ),
            array(
                array('null_on_all_empty' => true),
                array('year' => null, 'month' => null, 'day' => null, 'hour' => null, 'minute' => null),
                null
            ),
        );
    }

    /**
     * @expectedException \Laminas\Filter\Exception\RuntimeException
     */
    public function testInvalidInput()
    {
        $sut = new DateTimeSelectFilter();
        $sut->filter(array('year' => '2120', 'month' => '10', 'day' => '26', 'hour' => '12'));
    }
}
