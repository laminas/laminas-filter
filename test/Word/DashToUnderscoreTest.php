<?php

/**
 * @see       https://github.com/laminas/laminas-filter for the canonical source repository
 * @copyright https://github.com/laminas/laminas-filter/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-filter/blob/master/LICENSE.md New BSD License
 */

namespace LaminasTest\Filter\Word;

use Laminas\Filter\Word\DashToUnderscore as DashToUnderscoreFilter;

/**
 * Test class for Laminas_Filter_Word_DashToUnderscore.
 *
 * @group      Laminas_Filter
 */
class DashToUnderscoreTest extends \PHPUnit_Framework_TestCase
{
    public function testFilterSeparatesCamelCasedWordsWithDashes()
    {
        $string   = 'dash-separated-words';
        $filter   = new DashToUnderscoreFilter();
        $filtered = $filter($string);

        $this->assertNotEquals($string, $filtered);
        $this->assertEquals('dash_separated_words', $filtered);
    }
}
