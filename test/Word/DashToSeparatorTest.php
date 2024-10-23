<?php

declare(strict_types=1);

namespace LaminasTest\Filter\Word;

use Laminas\Filter\Word\DashToSeparator as DashToSeparatorFilter;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use stdClass;

class DashToSeparatorTest extends TestCase
{
    public function testFilterSeparatesDashedWordsWithDefaultSpaces(): void
    {
        $string   = 'dash-separated-words';
        $filter   = new DashToSeparatorFilter();
        $filtered = $filter($string);

        self::assertNotEquals($string, $filtered);
        self::assertSame('dash separated words', $filtered);
    }

    public function testFilterSeparatesDashedWordsWithSomeString(): void
    {
        $string   = 'dash-separated-words';
        $filter   = new DashToSeparatorFilter(['separator' => ':-:']);
        $filtered = $filter($string);

        self::assertNotEquals($string, $filtered);
        self::assertSame('dash:-:separated:-:words', $filtered);
    }

    public function testFilterSupportArray(): void
    {
        $filter = new DashToSeparatorFilter();

        $input = [
            'dash-separated-words',
            'something-different',
        ];

        $filtered = $filter($input);

        self::assertNotEquals($input, $filtered);
        self::assertSame(['dash separated words', 'something different'], $filtered);
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
        $filter = new DashToSeparatorFilter();

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
        $filter = new DashToSeparatorFilter();

        self::assertSame((string) $input, $filter($input));
    }
}
