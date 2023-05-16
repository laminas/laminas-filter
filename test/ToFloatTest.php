<?php

declare(strict_types=1);

namespace LaminasTest\Filter;

use Laminas\Filter\ToFloat as ToFloatFilter;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use stdClass;

class ToFloatTest extends TestCase
{
    /** @return array<string, array{0: mixed, 1: float}> */
    public static function filterableValuesProvider(): array
    {
        return [
            'string word' => ['string', 0.0],
            'string 1'    => ['1', 1.0],
            'string -1'   => ['-1', -1.0],
            'string 1.1'  => ['1.1', 1.1],
            'string -1.1' => ['-1.1', -1.1],
            'string 0.9'  => ['0.9', 0.9],
            'string -0.9' => ['-0.9', -0.9],
            'integer 1'   => [1, 1.0],
            'integer -1'  => [-1, -1.0],
            'true'        => [true, 1.0],
            'false'       => [false, 0.0],
            'float 1.1'   => [1.1, 1.1],
        ];
    }

    /**
     * Ensures that the filter follows expected behavior
     */
    #[DataProvider('filterableValuesProvider')]
    public function testCanFilterScalarValuesAsExpected(mixed $input, float $expectedOutput): void
    {
        $filter = new ToFloatFilter();
        self::assertSame($expectedOutput, $filter($input));
    }

    /** @return array<string, array{0: mixed}> */
    public static function unfilterableValuesProvider(): array
    {
        return [
            'null'   => [null],
            'array'  => [
                [
                    '1',
                    -1,
                ],
            ],
            'object' => [new stdClass()],
        ];
    }

    #[DataProvider('unfilterableValuesProvider')]
    public function testReturnsUnfilterableInputVerbatim(mixed $input): void
    {
        $filter = new ToFloatFilter();
        self::assertSame($input, $filter($input));
    }
}
