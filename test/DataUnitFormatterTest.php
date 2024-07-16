<?php

declare(strict_types=1);

namespace LaminasTest\Filter;

use Laminas\Filter\DataUnitFormatter as DataUnitFormatterFilter;
use Laminas\Filter\Exception;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class DataUnitFormatterTest extends TestCase
{
    #[DataProvider('decimalBytesTestProvider')]
    public function testDecimalBytes(float $value, string $expected): void
    {
        $filter = new DataUnitFormatterFilter([
            'mode' => DataUnitFormatterFilter::MODE_DECIMAL,
        ]);
        self::assertSame($expected, $filter->filter($value));
    }

    #[DataProvider('binaryBytesTestProvider')]
    public function testBinaryBytes(float $value, string $expected): void
    {
        $filter = new DataUnitFormatterFilter([
            'mode' => DataUnitFormatterFilter::MODE_BINARY,
        ]);
        self::assertSame($expected, $filter->filter($value));
    }

    public function testPrecision(): void
    {
        $filter = new DataUnitFormatterFilter([
            'precision' => 3,
        ]);

        self::assertSame('1.500 kB', $filter->filter(1500));
    }

    public function testSettingFalseMode(): void
    {
        $this->expectException(Exception\InvalidArgumentException::class);
        /** @psalm-suppress InvalidArgument */
        new DataUnitFormatterFilter([
            'mode' => 'invalid',
        ]);
    }

    /** @return list<array{0: float, 1: string}> */
    public static function decimalBytesTestProvider(): array
    {
        return [
            [0, '0 B'],
            [1, '1.00 B'],
            [1000 ** 1, '1.00 kB'],
            [1500 ** 1, '1.50 kB'],
            [1000 ** 2, '1.00 MB'],
            [1000 ** 3, '1.00 GB'],
            [1000 ** 4, '1.00 TB'],
            [1000 ** 5, '1.00 PB'],
            [1000 ** 6, '1.00 EB'],
            [1000 ** 7, '1.00 ZB'],
            [1000 ** 8, '1.00 YB'],
            [1000 ** 9, 1000 ** 9 . ' B'],
        ];
    }

    /** @return list<array{0: float, 1: string}> */
    public static function binaryBytesTestProvider(): array
    {
        return [
            [0, '0 B'],
            [1, '1.00 B'],
            [1024 ** 1, '1.00 KiB'],
            [1536 ** 1, '1.50 KiB'],
            [1024 ** 2, '1.00 MiB'],
            [1024 ** 3, '1.00 GiB'],
            [1024 ** 4, '1.00 TiB'],
            [1024 ** 5, '1.00 PiB'],
            [1024 ** 6, '1.00 EiB'],
            [1024 ** 7, '1.00 ZiB'],
            [1024 ** 8, '1.00 YiB'],
            [1024 ** 9, 1024 ** 9 . ' B'],
        ];
    }
}
