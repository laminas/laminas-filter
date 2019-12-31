<?php

/**
 * @see       https://github.com/laminas/laminas-filter for the canonical source repository
 * @copyright https://github.com/laminas/laminas-filter/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-filter/blob/master/LICENSE.md New BSD License
 */
namespace LaminasTest\Filter;

use Laminas\Filter\BaseName as BaseNameFilter;

/**
 * @group Laminas_Filter
 */
class BaseNameTest extends \PHPUnit_Framework_TestCase
{

    /**
     * Ensures that the filter follows expected behavior
     *
     * @return void
     */
    public function testBasic()
    {
        $filter = new BaseNameFilter();
        $valuesExpected = array(
            '/path/to/filename' => 'filename',
            '/path/to/filename.ext' => 'filename.ext'
        );
        foreach ($valuesExpected as $input => $output) {
            $this->assertEquals($output, $filter($input));
        }
    }

    public function returnUnfilteredDataProvider()
    {
        return array(
            array(null),
            array(new \stdClass()),
            array(array(
                '/path/to/filename',
                '/path/to/filename.ext'
            ))
        );
    }

    /**
     * @dataProvider returnUnfilteredDataProvider
     * @return void
     */
    public function testReturnUnfiltered($input)
    {
        $filter = new BaseNameFilter();

        $this->assertEquals($input, $filter($input));
    }
}
