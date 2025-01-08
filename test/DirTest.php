<?php

declare(strict_types=1);

namespace LaminasTest\Filter;

use Laminas\Filter\Dir as DirFilter;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use stdClass;

class DirTest extends TestCase
{
    /**
     * Ensures that the filter follows expected behavior
     */
    #[DataProvider('defaultSettingsDataProvider')]
    public function testBasic(string $input, string $expected): void
    {
        $filter = new DirFilter();

        self::assertSame($expected, $filter($input));
        self::assertSame($expected, $filter->__invoke($input));
        self::assertSame($expected, $filter->filter($input));
    }

    public static function defaultSettingsDataProvider(): array
    {
        return [
            ['12345', '.'],
            ['filename', '.'],
            ['/path/to/filename', '/path/to'],
            ['/path/to/filename.ext', '/path/to'],
        ];
    }

    /** @return list<array{0: mixed}> */
    public static function returnUnfilteredDataProvider(): array
    {
        return [
            [null],
            [new stdClass()],
            [''],
            [12345],
            [true],
            [false],
            [
                [
                    '/path/to/filename',
                    '/path/to/filename.ext',
                ],
            ],
        ];
    }

    #[DataProvider('returnUnfilteredDataProvider')]
    public function testReturnUnfiltered(mixed $input): void
    {
        $filter = new DirFilter();

        self::assertSame($input, $filter($input));
    }
}
