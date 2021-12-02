<?php

declare(strict_types=1);

namespace LaminasTest\Filter\Word;

use Laminas\Filter\Word\CamelCaseToSeparator as CamelCaseToSeparatorFilter;
use PHPUnit\Framework\TestCase;
use stdClass;

class CamelCaseToSeparatorTest extends TestCase
{
    public function testFilterSeparatesCamelCasedWordsWithSpacesByDefault()
    {
        $string   = 'CamelCasedWords';
        $filter   = new CamelCaseToSeparatorFilter();
        $filtered = $filter($string);

        $this->assertNotEquals($string, $filtered);
        $this->assertEquals('Camel Cased Words', $filtered);
    }

    public function testFilterSeparatesCamelCasedWordsWithProvidedSeparator()
    {
        $string   = 'CamelCasedWords';
        $filter   = new CamelCaseToSeparatorFilter(':-#');
        $filtered = $filter($string);

        $this->assertNotEquals($string, $filtered);
        $this->assertEquals('Camel:-#Cased:-#Words', $filtered);
    }

    public function testFilterSeperatesMultipleUppercasedLettersAndUnderscores()
    {
        $string   = 'TheseAre_SOME_CamelCASEDWords';
        $filter   = new CamelCaseToSeparatorFilter('_');
        $filtered = $filter($string);

        $this->assertNotEquals($string, $filtered);
        $this->assertEquals('These_Are_SOME_Camel_CASED_Words', $filtered);
    }

    /**
     * @return void
     */
    public function testFilterSupportArray()
    {
        $filter = new CamelCaseToSeparatorFilter();

        $input = [
            'CamelCasedWords',
            'somethingDifferent',
        ];

        $filtered = $filter($input);

        $this->assertNotEquals($input, $filtered);
        $this->assertEquals(['Camel Cased Words', 'something Different'], $filtered);
    }

    public function returnUnfilteredDataProvider()
    {
        return [
            [null],
            [new stdClass()],
        ];
    }

    /**
     * @dataProvider returnUnfilteredDataProvider
     * @return void
     */
    public function testReturnUnfiltered($input)
    {
        $filter = new CamelCaseToSeparatorFilter();

        $this->assertEquals($input, $filter($input));
    }
}
