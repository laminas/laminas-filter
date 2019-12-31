<?php

/**
 * @see       https://github.com/laminas/laminas-filter for the canonical source repository
 * @copyright https://github.com/laminas/laminas-filter/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-filter/blob/master/LICENSE.md New BSD License
 */

namespace ZendTest\Filter\Word;

use Zend\Filter\Word\DashToCamelCase as DashToCamelCaseFilter;

/**
 * Test class for Zend\Filter\Word\DashToCamelCase.
 *
 * @group      Zend_Filter
 */
class DashToCamelCaseTest extends \PHPUnit_Framework_TestCase
{
    public function testFilterSeparatesCamelCasedWordsWithDashes()
    {
        $string   = 'camel-cased-words';
        $filter   = new DashToCamelCaseFilter();
        $filtered = $filter($string);

        $this->assertNotEquals($string, $filtered);
        $this->assertEquals('CamelCasedWords', $filtered);
    }
}
