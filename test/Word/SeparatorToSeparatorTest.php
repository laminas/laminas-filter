<?php

declare(strict_types=1);

namespace LaminasTest\Filter\Word;

use Laminas\Filter\Word\SeparatorToSeparator as SeparatorToSeparatorFilter;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use stdClass;

class SeparatorToSeparatorTest extends TestCase
{
    public function testFilterSeparatesWordsByDefault(): void
    {
        $string   = 'dash separated words';
        $filter   = new SeparatorToSeparatorFilter();
        $filtered = $filter($string);

        self::assertNotEquals($string, $filtered);
        self::assertSame('dash-separated-words', $filtered);
    }

    public function testFilterSupportArray(): void
    {
        $filter = new SeparatorToSeparatorFilter();

        $input    = [
            'dash separated words',
            '=test something',
        ];
        $filtered = $filter($input);

        self::assertNotEquals($input, $filtered);
        self::assertSame([
            'dash-separated-words',
            '=test-something',
        ], $filtered);
    }

    public function testFilterSeparatesWordsWithSearchSpecified(): void
    {
        $string   = 'dash=separated=words';
        $filter   = new SeparatorToSeparatorFilter('=');
        $filtered = $filter($string);

        self::assertNotEquals($string, $filtered);
        self::assertSame('dash-separated-words', $filtered);
    }

    public function testFilterSeparatesWordsWithSearchAndReplacementSpecified(): void
    {
        $string   = 'dash=separated=words';
        $filter   = new SeparatorToSeparatorFilter('=', '?');
        $filtered = $filter($string);

        self::assertNotEquals($string, $filtered);
        self::assertSame('dash?separated?words', $filtered);
    }

    /** @return list<array{0: mixed}> */
    public static function returnUnfilteredDataProvider(): array
    {
        return [
            [null],
            [new stdClass()],
        ];
    }

    #[DataProvider('returnUnfilteredDataProvider')]
    public function testReturnUnfiltered(mixed $input): void
    {
        $filter = new SeparatorToSeparatorFilter('=', '?');

        self::assertSame($input, $filter($input));
    }

    /**
     * @return array<int|float|bool>[]
     */
    public static function returnNonStringScalarValues(): array
    {
        return [
            [1],
            [1.0],
            [true],
            [false],
        ];
    }

    #[DataProvider('returnNonStringScalarValues')]
    public function testShouldFilterNonStringScalarValues(float|bool|int $input): void
    {
        $filter = new SeparatorToSeparatorFilter('=', '?');

        self::assertSame((string) $input, $filter($input));
    }
}
