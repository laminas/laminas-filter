<?php

declare(strict_types=1);

namespace LaminasTest\Filter\Word;

use Laminas\Filter\Word\CamelCaseToDash as CamelCaseToDashFilter;
use PHPUnit\Framework\TestCase;

class CamelCaseToDashTest extends TestCase
{
    public function testFilterSeparatesCamelCasedWordsWithDashes(): void
    {
        $string   = 'CamelCasedWords';
        $filter   = new CamelCaseToDashFilter();
        $filtered = $filter($string);

        self::assertNotEquals($string, $filtered);
        self::assertSame('Camel-Cased-Words', $filtered);
    }
}
