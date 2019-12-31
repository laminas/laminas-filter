<?php

/**
 * @see       https://github.com/laminas/laminas-filter for the canonical source repository
 * @copyright https://github.com/laminas/laminas-filter/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-filter/blob/master/LICENSE.md New BSD License
 */

namespace LaminasTest\Filter\Word;

use Laminas\Filter\Word\SeparatorToDash as SeparatorToDashFilter;
use PHPUnit\Framework\TestCase;

class SeparatorToDashTest extends TestCase
{
    public function testFilterSeparatesDashedWordsWithDefaultSpaces()
    {
        $string   = 'dash separated words';
        $filter   = new SeparatorToDashFilter();
        $filtered = $filter($string);

        $this->assertNotEquals($string, $filtered);
        $this->assertEquals('dash-separated-words', $filtered);
    }

    public function testFilterSeparatesDashedWordsWithSomeString()
    {
        $string   = 'dash=separated=words';
        $filter   = new SeparatorToDashFilter('=');
        $filtered = $filter($string);

        $this->assertNotEquals($string, $filtered);
        $this->assertEquals('dash-separated-words', $filtered);
    }
}
