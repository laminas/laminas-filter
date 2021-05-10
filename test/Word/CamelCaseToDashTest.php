<?php

namespace LaminasTest\Filter\Word;

use Laminas\Filter\Word\CamelCaseToDash as CamelCaseToDashFilter;
use PHPUnit\Framework\TestCase;

class CamelCaseToDashTest extends TestCase
{
    public function testFilterSeparatesCamelCasedWordsWithDashes()
    {
        $string   = 'CamelCasedWords';
        $filter   = new CamelCaseToDashFilter();
        $filtered = $filter($string);

        $this->assertNotEquals($string, $filtered);
        $this->assertEquals('Camel-Cased-Words', $filtered);
    }
}
