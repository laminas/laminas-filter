<?php

declare(strict_types=1);

namespace LaminasTest\Filter;

use Laminas\Filter\ToString;
use LaminasTest\Filter\TestAsset\StringClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use stdClass;

final class ToStringTest extends TestCase
{
    /** @return list<array{0: mixed, 1: string}> */
    public static function returnBasicDataProvider(): array
    {
        return [
            [0, '0'],
            ['string', 'string'],
            [false, ''],
            [-1.1, '-1.1'],
            [new StringClass('test'), 'test'],
        ];
    }

    #[DataProvider('returnBasicDataProvider')]
    public function testBasic(mixed $input, string $output): void
    {
        $filter = new ToString();

        self::assertSame($output, $filter->filter($input));
    }

    #[DataProvider('returnBasicDataProvider')]
    public function testInvoke(mixed $input, string $output): void
    {
        $filter = new ToString();

        self::assertSame($output, $filter->__invoke($input));
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
