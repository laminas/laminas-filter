<?php

/**
 * @see       https://github.com/laminas/laminas-filter for the canonical source repository
 * @copyright https://github.com/laminas/laminas-filter/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-filter/blob/master/LICENSE.md New BSD License
 */

namespace LaminasTest\Filter\Word;

use Laminas\Filter\Word\CamelCaseToDash as CamelCaseToDashFilter;

/**
 * Test class for Laminas_Filter_Word_CamelCaseToDash.
 *
 * @group      Laminas_Filter
 */
class CamelCaseToDashTest extends \PHPUnit_Framework_TestCase
{
    public function testFilterSeparatesCamelCasedWordsWithDashes()
    {
        $string   = 'CamelCasedWords';
        $filter   = new CamelCaseToDashFilter();
        $filtered = $filter($string);

        $this->assertNotEquals($string, $filtered);
        $this->assertEquals('Camel-Cased-Words', $filtered);
    }
}
