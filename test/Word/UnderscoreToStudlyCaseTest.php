<?php

declare(strict_types=1);

namespace LaminasTest\Filter\Word;

use Laminas\Filter\Word\UnderscoreToStudlyCase;
use PHPUnit\Framework\TestCase;

class UnderscoreToStudlyCaseTest extends TestCase
{
    public function testFilterSeparatesStudlyCasedWordsWithDashes(): void
    {
        $string   = 'studly_cased_words';
        $filter   = new UnderscoreToStudlyCase();
        $filtered = $filter($string);

        $this->assertNotEquals($string, $filtered);
        $this->assertSame('studlyCasedWords', $filtered);
    }

    public function testSomeFilterValues(): void
    {
        $filter = new UnderscoreToStudlyCase();

        $string   = 'laminas_project';
        $filtered = $filter($string);
        $this->assertNotEquals($string, $filtered);
        $this->assertSame('laminasProject', $filtered);

        $string   = 'laminas_Project';
        $filtered = $filter($string);
        $this->assertNotEquals($string, $filtered);
        $this->assertSame('laminasProject', $filtered);

        $string   = 'laminasProject';
        $filtered = $filter($string);
        $this->assertSame('laminasProject', $filtered);

        $string   = 'laminas';
        $filtered = $filter($string);
        $this->assertSame('laminas', $filtered);

        $string   = '_laminas';
        $filtered = $filter($string);
        $this->assertNotEquals($string, $filtered);
        $this->assertSame('laminas', $filtered);

        $string   = '_laminas_project';
        $filtered = $filter($string);
        $this->assertNotEquals($string, $filtered);
        $this->assertSame('laminasProject', $filtered);
    }

    public function testFiltersArray(): void
    {
        $filter = new UnderscoreToStudlyCase();

        $string   = ['laminas_project', '_laminas_project'];
        $filtered = $filter($string);
        $this->assertNotEquals($string, $filtered);
        $this->assertSame(['laminasProject', 'laminasProject'], $filtered);
    }

    public function testWithEmpties(): void
    {
        $filter = new UnderscoreToStudlyCase();

        $string   = '';
        $filtered = $filter($string);
        $this->assertSame('', $filtered);

        $string   = ['', 'Laminas_Project'];
        $filtered = $filter($string);
        $this->assertSame(['', 'laminasProject'], $filtered);
    }
}
