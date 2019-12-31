<?php

/**
 * @see       https://github.com/laminas/laminas-filter for the canonical source repository
 * @copyright https://github.com/laminas/laminas-filter/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-filter/blob/master/LICENSE.md New BSD License
 */

namespace LaminasTest\Filter;

use Laminas\Filter\Dir as DirFilter;

/**
 * @category   Laminas
 * @package    Laminas_Filter
 * @subpackage UnitTests
 * @group      Laminas_Filter
 */
class DirTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Ensures that the filter follows expected behavior
     *
     * @return void
     */
    public function testBasic()
    {
        $filter = new DirFilter();
        $valuesExpected = array(
            'filename'              => '.',
            '/path/to/filename'     => '/path/to',
            '/path/to/filename.ext' => '/path/to'
            );
        foreach ($valuesExpected as $input => $output) {
            $this->assertEquals($output, $filter($input));
        }
    }
}
