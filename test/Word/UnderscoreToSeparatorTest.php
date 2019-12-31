<?php

/**
 * @see       https://github.com/laminas/laminas-filter for the canonical source repository
 * @copyright https://github.com/laminas/laminas-filter/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-filter/blob/master/LICENSE.md New BSD License
 */

namespace LaminasTest\Filter\Word;

use Laminas\Filter\Word\UnderscoreToSeparator as UnderscoreToSeparatorFilter;

/**
 * Test class for Laminas_Filter_Word_UnderscoreToSeparator.
 *
 * @category   Laminas
 * @package    Laminas_Filter
 * @subpackage UnitTests
 * @group      Laminas_Filter
 */
class UnderscoreToSeparatorTest extends \PHPUnit_Framework_TestCase
{
    public function testFilterSeparatesCamelCasedWordsDefaultSeparator()
    {
        $string   = 'underscore_separated_words';
        $filter   = new UnderscoreToSeparatorFilter();
        $filtered = $filter($string);

        $this->assertNotEquals($string, $filtered);
        $this->assertEquals('underscore separated words', $filtered);
    }

    public function testFilterSeparatesCamelCasedWordsProvidedSeparator()
    {
        $string   = 'underscore_separated_words';
        $filter   = new UnderscoreToSeparatorFilter(':=:');
        $filtered = $filter($string);

        $this->assertNotEquals($string, $filtered);
        $this->assertEquals('underscore:=:separated:=:words', $filtered);
    }

}
