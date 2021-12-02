<?php

declare(strict_types=1);

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
