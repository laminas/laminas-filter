<?php

/**
 * @see       https://github.com/laminas/laminas-filter for the canonical source repository
 * @copyright https://github.com/laminas/laminas-filter/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-filter/blob/master/LICENSE.md New BSD License
 */

namespace LaminasTest\Filter\Word;

use Laminas\Filter\Word\UnderscoreToStudlyCase;
use PHPUnit\Framework\TestCase;

class UnderscoreToStudlyCaseTest extends TestCase
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

        $string   = 'laminas_project';
        $filtered = $filter($string);
        $this->assertNotEquals($string, $filtered);
        $this->assertEquals('laminasProject', $filtered);

        $string   = 'laminas_Project';
        $filtered = $filter($string);
        $this->assertNotEquals($string, $filtered);
        $this->assertEquals('laminasProject', $filtered);

        $string   = 'laminasProject';
        $filtered = $filter($string);
        $this->assertEquals('laminasProject', $filtered);

        $string   = 'laminas';
        $filtered = $filter($string);
        $this->assertEquals('laminas', $filtered);

        $string   = '_laminas';
        $filtered = $filter($string);
        $this->assertNotEquals($string, $filtered);
        $this->assertEquals('laminas', $filtered);

        $string   = '_laminas_project';
        $filtered = $filter($string);
        $this->assertNotEquals($string, $filtered);
        $this->assertEquals('laminasProject', $filtered);
    }

    public function testFiltersArray()
    {
        $filter   = new UnderscoreToStudlyCase();

        $string   = ['laminas_project', '_laminas_project'];
        $filtered = $filter($string);
        $this->assertNotEquals($string, $filtered);
        $this->assertEquals(['laminasProject', 'laminasProject'], $filtered);
    }

    public function testWithEmpties()
    {
        $filter   = new UnderscoreToStudlyCase();

        $string   = '';
        $filtered = $filter($string);
        $this->assertEquals('', $filtered);

        $string   = ['', 'Laminas_Project'];
        $filtered = $filter($string);
        $this->assertEquals(['', 'laminasProject'], $filtered);
    }
}
