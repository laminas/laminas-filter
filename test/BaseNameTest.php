<?php

declare(strict_types=1);

namespace LaminasTest\Filter;

use Laminas\Filter\BaseName as BaseNameFilter;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use stdClass;

class BaseNameTest extends TestCase
{
    /**
     * Ensures that the filter follows expected behavior
     */
    public function testBasic(): void
    {
        $filter         = new BaseNameFilter();
        $valuesExpected = [
            '/path/to/filename'     => 'filename',
            '/path/to/filename.ext' => 'filename.ext',
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
            [1],
            [2.5],
            [true],
            [new stdClass()],
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
        $filter = new BaseNameFilter();

        self::assertSame($input, $filter($input));
    }
}
