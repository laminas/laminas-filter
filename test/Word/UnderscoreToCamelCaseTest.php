<?php

declare(strict_types=1);

namespace LaminasTest\Filter\Word;

use Laminas\Filter\Word\UnderscoreToCamelCase as UnderscoreToCamelCaseFilter;
use PHPUnit\Framework\TestCase;

class UnderscoreToCamelCaseTest extends TestCase
{
    public function testFilterSeparatesCamelCasedWordsWithDashes()
    {
        $string   = 'camel_cased_words';
        $filter   = new UnderscoreToCamelCaseFilter();
        $filtered = $filter($string);

        $this->assertNotEquals($string, $filtered);
        $this->assertEquals('CamelCasedWords', $filtered);
    }

    /**
     * Laminas-4097
     */
    public function testSomeFilterValues()
    {
        $filter = new UnderscoreToCamelCaseFilter();

        $string   = 'laminas_project';
        $filtered = $filter($string);
        $this->assertNotEquals($string, $filtered);
        $this->assertEquals('LaminasProject', $filtered);

        $string   = 'laminas_Project';
        $filtered = $filter($string);
        $this->assertNotEquals($string, $filtered);
        $this->assertEquals('LaminasProject', $filtered);

        $string   = 'laminasProject';
        $filtered = $filter($string);
        $this->assertNotEquals($string, $filtered);
        $this->assertEquals('LaminasProject', $filtered);

        $string   = 'laminasproject';
        $filtered = $filter($string);
        $this->assertNotEquals($string, $filtered);
        $this->assertEquals('Laminasproject', $filtered);

        $string   = '_laminasproject';
        $filtered = $filter($string);
        $this->assertNotEquals($string, $filtered);
        $this->assertEquals('Laminasproject', $filtered);

        $string   = '_laminas_project';
        $filtered = $filter($string);
        $this->assertNotEquals($string, $filtered);
        $this->assertEquals('LaminasProject', $filtered);
    }
}
