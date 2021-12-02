<?php

declare(strict_types=1);

namespace LaminasTest\Filter\Word;

use Laminas\Filter\Word\CamelCaseToUnderscore as CamelCaseToUnderscoreFilter;
use PHPUnit\Framework\TestCase;

class CamelCaseToUnderscoreTest extends TestCase
{
    public function testFilterSeparatesCamelCasedWordsWithUnderscores()
    {
        $string   = 'CamelCasedWords';
        $filter   = new CamelCaseToUnderscoreFilter();
        $filtered = $filter($string);

        $this->assertNotEquals($string, $filtered);
        $this->assertEquals('Camel_Cased_Words', $filtered);
    }

    public function testFilterSeperatingNumbersToUnterscore()
    {
        $string   = 'PaTitle';
        $filter   = new CamelCaseToUnderscoreFilter();
        $filtered = $filter($string);

        $this->assertNotEquals($string, $filtered);
        $this->assertEquals('Pa_Title', $filtered);

        $string   = 'Pa2Title';
        $filter   = new CamelCaseToUnderscoreFilter();
        $filtered = $filter($string);

        $this->assertNotEquals($string, $filtered);
        $this->assertEquals('Pa2_Title', $filtered);

        $string   = 'Pa2aTitle';
        $filter   = new CamelCaseToUnderscoreFilter();
        $filtered = $filter($string);

        $this->assertNotEquals($string, $filtered);
        $this->assertEquals('Pa2a_Title', $filtered);
    }
}
