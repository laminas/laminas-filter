<?php

declare(strict_types=1);

namespace LaminasTest\Filter;

use Laminas\Filter\ToString;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use stdClass;

class ToStringTest extends TestCase
{
    /** @return list<array{0: mixed}> */
    public static function returnBasicDataProvider(): array
    {
        return [
            [0, '0'],
            ['string', 'string'],
            [false, ''],
            [-1.1, '-1.1'],
        ];
    }

    #[DataProvider('returnBasicDataProvider')]
    public function testBasic(mixed $input, string $output): void
    {
        $filter = new ToString();

        self::assertSame($output, $filter($input));
    }

    /** @return list<array{0: mixed}> */
    public static function returnUnfilteredDataProvider(): array
    {
        return [
            [null],
            [new stdClass()],
            [
                [
                    'foo',
                    false,
                ],
            ],
        ];
    }

    #[DataProvider('returnUnfilteredDataProvider')]
    public function testReturnUnfiltered(mixed $input): void
    {
        $filter = new ToString();

        self::assertSame($input, $filter($input));
    }
}
