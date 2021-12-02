<?php

declare(strict_types=1);

namespace LaminasTest\Filter\Word;

use Laminas\Filter\Word\UnderscoreToSeparator as UnderscoreToSeparatorFilter;
use PHPUnit\Framework\TestCase;

class UnderscoreToSeparatorTest extends TestCase
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
