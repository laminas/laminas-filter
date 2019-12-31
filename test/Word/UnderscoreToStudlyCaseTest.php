<?php

/**
 * @see       https://github.com/laminas/laminas-filter for the canonical source repository
 * @copyright https://github.com/laminas/laminas-filter/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-filter/blob/master/LICENSE.md New BSD License
 */

namespace LaminasTest\Filter\Word;

use Laminas\Filter\Word\UnderscoreToStudlyCase;

/**
 * Test class for Laminas\Filter\Word\UnderscoreToStudlyCase.
 *
 * @group      Laminas_Filter
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

        $string   = 'laminas_framework';
        $filtered = $filter($string);
        $this->assertNotEquals($string, $filtered);
        $this->assertEquals('laminasFramework', $filtered);

        $string   = 'laminas_Framework';
        $filtered = $filter($string);
        $this->assertNotEquals($string, $filtered);
        $this->assertEquals('laminasFramework', $filtered);

        $string   = 'laminasFramework';
        $filtered = $filter($string);
        $this->assertEquals('laminasFramework', $filtered);

        $string   = 'laminas';
        $filtered = $filter($string);
        $this->assertEquals('laminas', $filtered);

        $string   = '_laminas';
        $filtered = $filter($string);
        $this->assertNotEquals($string, $filtered);
        $this->assertEquals('laminas', $filtered);

        $string   = '_laminas_framework';
        $filtered = $filter($string);
        $this->assertNotEquals($string, $filtered);
        $this->assertEquals('laminasFramework', $filtered);
    }

    public function testFiltersArray()
    {
        $filter   = new UnderscoreToStudlyCase();

        $string   = ['laminas_framework', '_laminas_framework'];
        $filtered = $filter($string);
        $this->assertNotEquals($string, $filtered);
        $this->assertEquals(['laminasFramework', 'laminasFramework'], $filtered);
    }

    public function testWithEmpties()
    {
        $filter   = new UnderscoreToStudlyCase();

        $string   = '';
        $filtered = $filter($string);
        $this->assertEquals('', $filtered);

        $string   = ['', 'Laminas_Framework'];
        $filtered = $filter($string);
        $this->assertEquals(['', 'laminasFramework'], $filtered);
    }
}
