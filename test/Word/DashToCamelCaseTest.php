<?php

declare(strict_types=1);

namespace LaminasTest\Filter\Word;

use Laminas\Filter\Word\DashToCamelCase as DashToCamelCaseFilter;
use PHPUnit\Framework\TestCase;

class DashToCamelCaseTest extends TestCase
{
    public function testFilterSeparatesCamelCasedWordsWithDashes()
    {
        $string   = 'camel-cased-words';
        $filter   = new DashToCamelCaseFilter();
        $filtered = $filter($string);

        $this->assertNotEquals($string, $filtered);
        $this->assertEquals('CamelCasedWords', $filtered);
    }
}
