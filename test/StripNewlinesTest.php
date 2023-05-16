<?php

declare(strict_types=1);

namespace LaminasTest\Filter;

use Laminas\Filter\StripNewlines as StripNewlinesFilter;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use stdClass;

use function array_keys;
use function array_values;

class StripNewlinesTest extends TestCase
{
    /**
     * Ensures that the filter follows expected behavior
     */
    public function testBasic(): void
    {
        $filter         = new StripNewlinesFilter();
        $valuesExpected = [
            ''                                    => '',
            "\n"                                  => '',
            "\r"                                  => '',
            "\r\n"                                => '',
            '\n'                                  => '\n',
            '\r'                                  => '\r',
            '\r\n'                                => '\r\n',
            "Some text\nthat we have\r\nstuff in" => 'Some textthat we havestuff in',
        ];
        foreach ($valuesExpected as $input => $output) {
            self::assertSame($output, $filter($input));
        }
    }

    public function testArrayValues(): void
    {
        $filter   = new StripNewlinesFilter();
        $expected = [
            "Some text\nthat we have\r\nstuff in" => 'Some textthat we havestuff in',
            "Some text\n"                         => 'Some text',
        ];
        self::assertSame(array_values($expected), $filter(array_keys($expected)));
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
        $filter = new StripNewlinesFilter();

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
        $filter = new StripNewlinesFilter();

        self::assertSame((string) $input, $filter($input));
    }
}
