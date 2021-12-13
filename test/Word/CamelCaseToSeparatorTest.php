<?php

declare(strict_types=1);

namespace LaminasTest\Filter\Word;

use Laminas\Filter\Word\CamelCaseToSeparator as CamelCaseToSeparatorFilter;
use PHPUnit\Framework\TestCase;
use stdClass;

class CamelCaseToSeparatorTest extends TestCase
{
    public function testFilterSeparatesCamelCasedWordsWithSpacesByDefault(): void
    {
        $string   = 'CamelCasedWords';
        $filter   = new CamelCaseToSeparatorFilter();
        $filtered = $filter($string);

        $this->assertNotEquals($string, $filtered);
        $this->assertSame('Camel Cased Words', $filtered);
    }

    public function testFilterSeparatesCamelCasedWordsWithProvidedSeparator(): void
    {
        $string   = 'CamelCasedWords';
        $filter   = new CamelCaseToSeparatorFilter(':-#');
        $filtered = $filter($string);

        $this->assertNotEquals($string, $filtered);
        $this->assertSame('Camel:-#Cased:-#Words', $filtered);
    }

    public function testFilterSeperatesMultipleUppercasedLettersAndUnderscores(): void
    {
        $string   = 'TheseAre_SOME_CamelCASEDWords';
        $filter   = new CamelCaseToSeparatorFilter('_');
        $filtered = $filter($string);

        $this->assertNotEquals($string, $filtered);
        $this->assertSame('These_Are_SOME_Camel_CASED_Words', $filtered);
    }

    public function testFilterSupportArray(): void
    {
        $filter = new CamelCaseToSeparatorFilter();

        $input = [
            'CamelCasedWords',
            'somethingDifferent',
        ];

        $filtered = $filter($input);

        $this->assertNotEquals($input, $filtered);
        $this->assertSame(['Camel Cased Words', 'something Different'], $filtered);
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
     */
    public function testReturnUnfiltered($input): void
    {
        $filter = new CamelCaseToSeparatorFilter();

        $this->assertSame($input, $filter($input));
    }

    /**
     * @return array<int|float|bool>[]
     */
    public function returnNonStringScalarValues(): array
    {
        return [
            [1],
            [1.0],
            [true],
            [false],
        ];
    }

    /**
     * @dataProvider returnNonStringScalarValues
     * @param int|float|bool $input
     */
    public function testShouldFilterNonStringScalarValues($input): void
    {
        $filter = new CamelCaseToSeparatorFilter();

        $this->assertSame((string) $input, $filter($input));
    }
}
