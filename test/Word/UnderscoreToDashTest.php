<?php

/**
 * @see       https://github.com/laminas/laminas-filter for the canonical source repository
 * @copyright https://github.com/laminas/laminas-filter/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-filter/blob/master/LICENSE.md New BSD License
 */

namespace ZendTest\Filter\Word;

use Zend\Filter\Word\UnderscoreToDash as UnderscoreToDashFilter;

/**
 * Test class for Zend\Filter\Word\UnderscoreToDash.
 *
 * @group      Zend_Filter
 */
class UnderscoreToDashTest extends \PHPUnit_Framework_TestCase
{
    public function testFilterSeparatesCamelCasedWordsWithDashes()
    {
        $string   = 'underscore_separated_words';
        $filter   = new UnderscoreToDashFilter();
        $filtered = $filter($string);

        $this->assertNotEquals($string, $filtered);
        $this->assertEquals('underscore-separated-words', $filtered);
    }
}
