<?php

declare(strict_types=1);

namespace LaminasTest\Filter\Word;

use Laminas\Filter\Word\DashToUnderscore as DashToUnderscoreFilter;
use PHPUnit\Framework\TestCase;

class DashToUnderscoreTest extends TestCase
{
    public function testFilterSeparatesCamelCasedWordsWithDashes()
    {
        $string   = 'dash-separated-words';
        $filter   = new DashToUnderscoreFilter();
        $filtered = $filter($string);

        $this->assertNotEquals($string, $filtered);
        $this->assertEquals('dash_separated_words', $filtered);
    }
}
