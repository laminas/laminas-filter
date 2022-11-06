<?php

declare(strict_types=1);

namespace LaminasTest\Filter\Word;

use Laminas\Filter\Word\UnderscoreToCamelCase as UnderscoreToCamelCaseFilter;
use PHPUnit\Framework\TestCase;

class UnderscoreToCamelCaseTest extends TestCase
{
    public function testFilterSeparatesCamelCasedWordsWithDashes(): void
    {
        $string   = 'camel_cased_words';
        $filter   = new UnderscoreToCamelCaseFilter();
        $filtered = $filter($string);

        self::assertNotEquals($string, $filtered);
        self::assertSame('CamelCasedWords', $filtered);
    }

    /**
     * Laminas-4097
     */
    public function testSomeFilterValues(): void
    {
        $filter = new UnderscoreToCamelCaseFilter();

        $string   = 'laminas_project';
        $filtered = $filter($string);
        self::assertNotEquals($string, $filtered);
        self::assertSame('LaminasProject', $filtered);

        $string   = 'laminas_Project';
        $filtered = $filter($string);
        self::assertNotEquals($string, $filtered);
        self::assertSame('LaminasProject', $filtered);

        $string   = 'laminasProject';
        $filtered = $filter($string);
        self::assertNotEquals($string, $filtered);
        self::assertSame('LaminasProject', $filtered);

        $string   = 'laminasproject';
        $filtered = $filter($string);
        self::assertNotEquals($string, $filtered);
        self::assertSame('Laminasproject', $filtered);

        $string   = '_laminasproject';
        $filtered = $filter($string);
        self::assertNotEquals($string, $filtered);
        self::assertSame('Laminasproject', $filtered);

        $string   = '_laminas_project';
        $filtered = $filter($string);
        self::assertNotEquals($string, $filtered);
        self::assertSame('LaminasProject', $filtered);
    }
}
