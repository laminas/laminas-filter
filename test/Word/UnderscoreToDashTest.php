<?php

/**
 * @see       https://github.com/laminas/laminas-filter for the canonical source repository
 * @copyright https://github.com/laminas/laminas-filter/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-filter/blob/master/LICENSE.md New BSD License
 */

namespace LaminasTest\Filter\Word;

use Laminas\Filter\Word\UnderscoreToDash as UnderscoreToDashFilter;
use PHPUnit\Framework\TestCase;

class UnderscoreToDashTest extends TestCase
{
    public function testFilterSeparatesCamelCasedWordsWithDashes()
    {
        $string   = 'underscore_separated_words';
        $filter   = new UnderscoreToDashFilter();
        $filtered = $filter($string);

        $this->assertNotEquals($string, $filtered);
        $this->assertEquals('underscore-separated-words', $filtered);
    }
}
