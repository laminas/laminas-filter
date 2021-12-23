<?php

declare(strict_types=1);

namespace LaminasTest\Filter\Word;

use Laminas\Filter\Word\UnderscoreToDash as UnderscoreToDashFilter;
use PHPUnit\Framework\TestCase;

class UnderscoreToDashTest extends TestCase
{
    public function testFilterSeparatesCamelCasedWordsWithDashes(): void
    {
        $string   = 'underscore_separated_words';
        $filter   = new UnderscoreToDashFilter();
        $filtered = $filter($string);

        $this->assertNotEquals($string, $filtered);
        $this->assertSame('underscore-separated-words', $filtered);
    }
}
