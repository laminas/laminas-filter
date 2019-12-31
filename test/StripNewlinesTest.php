<?php

/**
 * @see       https://github.com/laminas/laminas-filter for the canonical source repository
 * @copyright https://github.com/laminas/laminas-filter/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-filter/blob/master/LICENSE.md New BSD License
 */
namespace ZendTest\Filter;

use Zend\Filter\StripNewlines as StripNewlinesFilter;

/**
 * @group Zend_Filter
 */
class StripNewlinesTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Ensures that the filter follows expected behavior
     *
     * @return void
     */
    public function testBasic()
    {
        $filter = new StripNewLinesFilter();
        $valuesExpected = array(
            '' => '',
            "\n" => '',
            "\r" => '',
            "\r\n" => '',
            '\n' => '\n',
            '\r' => '\r',
            '\r\n' => '\r\n',
            "Some text\nthat we have\r\nstuff in" => 'Some textthat we havestuff in'
        );
        foreach ($valuesExpected as $input => $output) {
            $this->assertEquals($output, $filter($input));
        }
    }

    /**
     *
     * @return void
     */
    public function testArrayValues()
    {
        $filter = new StripNewLinesFilter();
        $expected = array(
            "Some text\nthat we have\r\nstuff in" => 'Some textthat we havestuff in',
            "Some text\n" => 'Some text'
        );
        $this->assertEquals(array_values($expected), $filter(array_keys($expected)));
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
        $filter = new StripNewLinesFilter();

        $this->assertEquals($input, $filter($input));
    }
}
