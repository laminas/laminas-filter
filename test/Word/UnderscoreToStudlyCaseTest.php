<?php

/**
 * @see       https://github.com/laminas/laminas-filter for the canonical source repository
 * @copyright https://github.com/laminas/laminas-filter/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-filter/blob/master/LICENSE.md New BSD License
 */

namespace ZendTest\Filter\Word;

use Zend\Filter\Word\UnderscoreToStudlyCase;

/**
 * Test class for Zend\Filter\Word\UnderscoreToStudlyCase.
 *
 * @group      Zend_Filter
 */
class UnderscoreToStudlyCaseTest extends \PHPUnit_Framework_TestCase
{
    public function testFilterSeparatesStudlyCasedWordsWithDashes()
    {
        $string   = 'studly_cased_words';
        $filter   = new UnderscoreToStudlyCase();
        $filtered = $filter($string);

        $this->assertNotEquals($string, $filtered);
        $this->assertEquals('studlyCasedWords', $filtered);
    }

    public function testSomeFilterValues()
    {
        $filter   = new UnderscoreToStudlyCase();

        $string   = 'zend_framework';
        $filtered = $filter($string);
        $this->assertNotEquals($string, $filtered);
        $this->assertEquals('zendFramework', $filtered);

        $string   = 'zend_Framework';
        $filtered = $filter($string);
        $this->assertNotEquals($string, $filtered);
        $this->assertEquals('zendFramework', $filtered);

        $string   = 'zendFramework';
        $filtered = $filter($string);
        $this->assertEquals('zendFramework', $filtered);

        $string   = 'zendframework';
        $filtered = $filter($string);
        $this->assertEquals('zendframework', $filtered);

        $string   = '_zendframework';
        $filtered = $filter($string);
        $this->assertNotEquals($string, $filtered);
        $this->assertEquals('zendframework', $filtered);

        $string   = '_zend_framework';
        $filtered = $filter($string);
        $this->assertNotEquals($string, $filtered);
        $this->assertEquals('zendFramework', $filtered);
    }

    public function testFiltersArray()
    {
        $filter   = new UnderscoreToStudlyCase();

        $string   = array('zend_framework', '_zend_framework');
        $filtered = $filter($string);
        $this->assertNotEquals($string, $filtered);
        $this->assertEquals(array('zendFramework', 'zendFramework'), $filtered);
    }

    public function testWithEmpties()
    {
        $filter   = new UnderscoreToStudlyCase();

        $string   = '';
        $filtered = $filter($string);
        $this->assertEquals('', $filtered);

        $string   = array('', 'Zend_Framework');
        $filtered = $filter($string);
        $this->assertEquals(array('', 'zendFramework'), $filtered);
    }
}
