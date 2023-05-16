<?php

declare(strict_types=1);

namespace LaminasTest\Filter;

use Laminas\Filter\ToInt as ToIntFilter;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use stdClass;

class ToIntTest extends TestCase
{
    /**
     * Ensures that the filter follows expected behavior
     */
    public function testBasic(): void
    {
        $filter = new ToIntFilter();

        $valuesExpected = [
            'string' => 0,
            '1'      => 1,
            '-1'     => -1,
            '1.1'    => 1,
            '-1.1'   => -1,
            '0.9'    => 0,
            '-0.9'   => 0,
        ];
        foreach ($valuesExpected as $input => $output) {
            self::assertSame($output, $filter($input));
        }
    }

    /** @return list<array{0: mixed}> */
    public static function returnUnfilteredDataProvider(): array
    {
        return [
            [null],
            [new stdClass()],
            [
                [
                    '1',
                    -1,
                ],
            ],
        ];
    }

    #[DataProvider('returnUnfilteredDataProvider')]
    public function testReturnUnfiltered(mixed $input): void
    {
        $filter = new ToIntFilter();

        self::assertSame($input, $filter($input));
    }
}
