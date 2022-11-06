<?php

declare(strict_types=1);

namespace LaminasTest\Filter\Word;

use Laminas\Filter\Word\SeparatorToDash as SeparatorToDashFilter;
use PHPUnit\Framework\TestCase;

class SeparatorToDashTest extends TestCase
{
    public function testFilterSeparatesDashedWordsWithDefaultSpaces(): void
    {
        $string   = 'dash separated words';
        $filter   = new SeparatorToDashFilter();
        $filtered = $filter($string);

        self::assertNotEquals($string, $filtered);
        self::assertSame('dash-separated-words', $filtered);
    }

    public function testFilterSeparatesDashedWordsWithSomeString(): void
    {
        $string   = 'dash=separated=words';
        $filter   = new SeparatorToDashFilter('=');
        $filtered = $filter($string);

        self::assertNotEquals($string, $filtered);
        self::assertSame('dash-separated-words', $filtered);
    }
}
