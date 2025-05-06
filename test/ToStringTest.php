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
    /** @return list<array{0: mixed, 1: mixed}> */
    public static function returnBasicDataProvider(): array
    {
        return [
            [0, '0'],
            ['string', 'string'],
            [false, ''],
            [true, '1'],
            [-1.1, '-1.1'],
            [new StringClass('test'), 'test'],
            [
                [0, 'string', false, -1.1, new StringClass('test'), [true, null]],
                ['0', 'string', '', '-1.1', 'test', ['1', null]],
            ],
        ];
    }

    #[DataProvider('returnBasicDataProvider')]
    public function testBasic(mixed $input, mixed $output): void
    {
        $filter = new ToString();

        self::assertSame($output, $filter->filter($input));
    }

    #[DataProvider('returnBasicDataProvider')]
    public function testInvoke(mixed $input, mixed $output): void
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
        ];
    }

    #[DataProvider('returnUnfilteredDataProvider')]
    public function testReturnUnfiltered(mixed $input): void
    {
        $filter = new ToString();

        self::assertSame($input, $filter($input));
    }
}
