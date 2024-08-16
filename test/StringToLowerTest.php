<?php

declare(strict_types=1);

namespace LaminasTest\Filter;

use Laminas\Filter\Exception;
use Laminas\Filter\StringToLower as StringToLowerFilter;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use stdClass;

class StringToLowerTest extends TestCase
{
    private StringToLowerFilter $filter;

    public function setUp(): void
    {
        $this->filter = new StringToLowerFilter();
    }

    /**
     * Ensures that the filter follows expected behavior
     */
    public function testBasic(): void
    {
        $filter         = $this->filter;
        $valuesExpected = [
            'string' => 'string',
            'aBc1@3' => 'abc1@3',
            'A b C'  => 'a b c',
        ];

        foreach ($valuesExpected as $input => $output) {
            self::assertSame($output, $filter($input));
        }
    }

    /**
     * Ensures that the filter follows expected behavior with
     * specified encoding
     */
    public function testWithEncoding(): void
    {
        $filter         = new StringToLowerFilter(['encoding' => 'utf-8']);
        $valuesExpected = [
            'Ü'     => 'ü',
            'Ñ'     => 'ñ',
            'ÜÑ123' => 'üñ123',
        ];

        foreach ($valuesExpected as $input => $output) {
            self::assertSame($output, $filter($input));
        }
    }

    public function testFalseEncoding(): void
    {
        $this->expectException(Exception\InvalidArgumentException::class);
        $this->expectExceptionMessage('is not supported');
        new StringToLowerFilter(['encoding' => 'aaaaa']);
    }

    /**
     * @Laminas-9058
     */
    public function testCaseInsensitiveEncoding(): void
    {
        $valuesExpected = [
            'Ü'     => 'ü',
            'Ñ'     => 'ñ',
            'ÜÑ123' => 'üñ123',
        ];

        $filter = new StringToLowerFilter(['encoding' => 'UTF-8']);
        foreach ($valuesExpected as $input => $output) {
            self::assertSame($output, $filter($input));
        }

        $filter = new StringToLowerFilter(['encoding' => 'utf-8']);
        foreach ($valuesExpected as $input => $output) {
            self::assertSame($output, $filter($input));
        }

        $filter = new StringToLowerFilter(['encoding' => 'UtF-8']);
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
                    'UPPER CASE WRITTEN',
                    'This should stay the same',
                ],
            ],
        ];
    }

    #[DataProvider('returnUnfilteredDataProvider')]
    public function testReturnUnfiltered(mixed $input): void
    {
        self::assertSame($input, $this->filter->filter($input));
    }
}
