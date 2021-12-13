<?php

declare(strict_types=1);

namespace LaminasTest\Filter\Word;

use Laminas\Filter\Word\SeparatorToCamelCase as SeparatorToCamelCaseFilter;
use PHPUnit\Framework\TestCase;
use stdClass;

use function extension_loaded;

class SeparatorToCamelCaseTest extends TestCase
{
    public function testFilterSeparatesCamelCasedWordsWithSpacesByDefault(): void
    {
        $string   = 'camel cased words';
        $filter   = new SeparatorToCamelCaseFilter();
        $filtered = $filter($string);

        $this->assertNotEquals($string, $filtered);
        $this->assertSame('CamelCasedWords', $filtered);
    }

    public function testFilterSeparatesCamelCasedWordsWithProvidedSeparator(): void
    {
        $string   = 'camel:-:cased:-:Words';
        $filter   = new SeparatorToCamelCaseFilter(':-:');
        $filtered = $filter($string);

        $this->assertNotEquals($string, $filtered);
        $this->assertSame('CamelCasedWords', $filtered);
    }

    /**
     * @group Laminas-10517
     */
    public function testFilterSeparatesUniCodeCamelCasedWordsWithProvidedSeparator(): void
    {
        if (! extension_loaded('mbstring')) {
            $this->markTestSkipped('Extension mbstring not available');
        }

        $string   = 'camel:-:cased:-:Words';
        $filter   = new SeparatorToCamelCaseFilter(':-:');
        $filtered = $filter($string);

        $this->assertNotEquals($string, $filtered);
        $this->assertSame('CamelCasedWords', $filtered);
    }

    /**
     * @group Laminas-10517
     */
    public function testFilterSeparatesUniCodeCamelCasedUserWordsWithProvidedSeparator(): void
    {
        if (! extension_loaded('mbstring')) {
            $this->markTestSkipped('Extension mbstring not available');
        }

        $string   = 'test šuma';
        $filter   = new SeparatorToCamelCaseFilter(' ');
        $filtered = $filter($string);

        $this->assertNotEquals($string, $filtered);
        $this->assertSame('TestŠuma', $filtered);
    }

    /**
     * @group 6151
     */
    public function testFilterSeparatesCamelCasedNonAlphaWordsWithProvidedSeparator(): void
    {
        $string   = 'user_2_user';
        $filter   = new SeparatorToCamelCaseFilter('_');
        $filtered = $filter($string);

        $this->assertNotEquals($string, $filtered);
        $this->assertSame('User2User', $filtered);
    }

    public function testFilterSupportArray(): void
    {
        $filter = new SeparatorToCamelCaseFilter();

        $input = [
            'camel cased words',
            'something different',
        ];

        $filtered = $filter($input);

        $this->assertNotEquals($input, $filtered);
        $this->assertSame(['CamelCasedWords', 'SomethingDifferent'], $filtered);
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
        $filter = new SeparatorToCamelCaseFilter();

        $this->assertSame($input, $filter($input));
    }
}
