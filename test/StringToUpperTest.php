<?php

declare(strict_types=1);

namespace LaminasTest\Filter;

use Laminas\Filter\Exception;
use Laminas\Filter\StringToUpper as StringToUpperFilter;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use stdClass;

class StringToUpperTest extends TestCase
{
    private StringToUpperFilter $filter;

    public function setUp(): void
    {
        $this->filter = new StringToUpperFilter();
    }

    /**
     * Ensures that the filter follows expected behavior
     */
    public function testBasic(): void
    {
        $filter         = $this->filter;
        $valuesExpected = [
            'STRING' => 'STRING',
            'ABC1@3' => 'ABC1@3',
            'A b C'  => 'A B C',
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
        $filter         = new StringToUpperFilter(['encoding' => 'utf-8']);
        $valuesExpected = [
            'ü'     => 'Ü',
            'ñ'     => 'Ñ',
            'üñ123' => 'ÜÑ123',
        ];

        foreach ($valuesExpected as $input => $output) {
            self::assertSame($output, $filter($input));
        }
    }

    public function testFalseEncoding(): void
    {
        $this->expectException(Exception\InvalidArgumentException::class);
        $this->expectExceptionMessage('is not supported');
        new StringToUpperFilter(['encoding' => 'aaaaa']);
    }

    /**
     *  @Laminas-9058
     */
    public function testCaseInsensitiveEncoding(): void
    {
        $valuesExpected = [
            'ü'     => 'Ü',
            'ñ'     => 'Ñ',
            'üñ123' => 'ÜÑ123',
        ];

        $filter = new StringToUpperFilter(['encoding' => 'UTF-8']);
        foreach ($valuesExpected as $input => $output) {
            self::assertSame($output, $filter($input));
        }

        $filter = new StringToUpperFilter(['encoding' => 'utf-8']);
        foreach ($valuesExpected as $input => $output) {
            self::assertSame($output, $filter($input));
        }

        new StringToUpperFilter(['encoding' => 'UtF-8']);
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
                    'lower case written',
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
