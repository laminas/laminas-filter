<?php

declare(strict_types=1);

namespace LaminasTest\Filter\Word;

use Laminas\Filter\Word\CamelCaseToUnderscore as CamelCaseToUnderscoreFilter;
use PHPUnit\Framework\TestCase;

class CamelCaseToUnderscoreTest extends TestCase
{
    public function testFilterSeparatesCamelCasedWordsWithUnderscores(): void
    {
        $string   = 'CamelCasedWords';
        $filter   = new CamelCaseToUnderscoreFilter();
        $filtered = $filter($string);

        self::assertNotEquals($string, $filtered);
        self::assertSame('Camel_Cased_Words', $filtered);
    }

    public function testFilterSeparatingNumbersToUnderscore(): void
    {
        $string   = 'PaTitle';
        $filter   = new CamelCaseToUnderscoreFilter();
        $filtered = $filter($string);

        self::assertNotEquals($string, $filtered);
        self::assertSame('Pa_Title', $filtered);

        $string   = 'Pa2Title';
        $filter   = new CamelCaseToUnderscoreFilter();
        $filtered = $filter($string);

        self::assertNotEquals($string, $filtered);
        self::assertSame('Pa2_Title', $filtered);

        $string   = 'Pa2aTitle';
        $filter   = new CamelCaseToUnderscoreFilter();
        $filtered = $filter($string);

        self::assertNotEquals($string, $filtered);
        self::assertSame('Pa2a_Title', $filtered);
    }
}
