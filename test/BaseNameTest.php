<?php

/**
 * @see       https://github.com/laminas/laminas-filter for the canonical source repository
 * @copyright https://github.com/laminas/laminas-filter/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-filter/blob/master/LICENSE.md New BSD License
 */

namespace LaminasTest\Filter;

use Laminas\Filter\BaseName as BaseNameFilter;
use Laminas\Stdlib\ErrorHandler;

/**
 * @group      Laminas_Filter
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
            '/path/to/filename'     => 'filename',
            '/path/to/filename.ext' => 'filename.ext'
            );
        foreach ($valuesExpected as $input => $output) {
            $this->assertEquals($output, $filter($input));
        }
    }

    /**
     * Ensures that a warning is raised if array is used
     *
     * @return void
     */
    public function testWarningIsRaisedIfArrayUsed()
    {
        $filter = new BaseNameFilter();
        $input = array('/path/to/filename', '/path/to/filename.ext');

        ErrorHandler::start(E_USER_WARNING);
        $filtered = $filter->filter($input);
        $err = ErrorHandler::stop();

        $this->assertEquals($input, $filtered);
        $this->assertInstanceOf('ErrorException', $err);
        $this->assertContains('cannot filter', $err->getMessage());
    }

    /**
     * @return void
     */
    public function testReturnsNullIfNullIsUsed()
    {
        $filter   = new BaseNameFilter();
        $filtered = $filter->filter(null);
        $this->assertNull($filtered);
    }
}
