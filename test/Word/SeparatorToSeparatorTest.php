<?php

declare(strict_types=1);

namespace LaminasTest\Filter\Word;

use Laminas\Filter\Word\SeparatorToSeparator as SeparatorToSeparatorFilter;
use PHPUnit\Framework\TestCase;
use stdClass;

class SeparatorToSeparatorTest extends TestCase
{
    public function testFilterSeparatesWordsByDefault(): void
    {
        $string   = 'dash separated words';
        $filter   = new SeparatorToSeparatorFilter();
        $filtered = $filter($string);

        $this->assertNotEquals($string, $filtered);
        $this->assertSame('dash-separated-words', $filtered);
    }

    public function testFilterSupportArray(): void
    {
        $filter = new SeparatorToSeparatorFilter();

        $input    = [
            'dash separated words',
            '=test something',
        ];
        $filtered = $filter($input);

        $this->assertNotEquals($input, $filtered);
        $this->assertSame([
            'dash-separated-words',
            '=test-something',
        ], $filtered);
    }

    public function testFilterSeparatesWordsWithSearchSpecified(): void
    {
        $string   = 'dash=separated=words';
        $filter   = new SeparatorToSeparatorFilter('=');
        $filtered = $filter($string);

        $this->assertNotEquals($string, $filtered);
        $this->assertSame('dash-separated-words', $filtered);
    }

    public function testFilterSeparatesWordsWithSearchAndReplacementSpecified(): void
    {
        $string   = 'dash=separated=words';
        $filter   = new SeparatorToSeparatorFilter('=', '?');
        $filtered = $filter($string);

        $this->assertNotEquals($string, $filtered);
        $this->assertSame('dash?separated?words', $filtered);
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
        $filter = new SeparatorToSeparatorFilter('=', '?');

        $this->assertSame($input, $filter($input));
    }
}
