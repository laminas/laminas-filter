<?php

/**
 * @see       https://github.com/laminas/laminas-filter for the canonical source repository
 * @copyright https://github.com/laminas/laminas-filter/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-filter/blob/master/LICENSE.md New BSD License
 */

namespace LaminasTest\Filter;

use Laminas\Filter\StripNewlines as StripNewlinesFilter;

/**
 * @group      Laminas_Filter
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
}
