<?php

declare(strict_types=1);

namespace LaminasTest\Filter;

use Laminas\Filter\Exception\InvalidArgumentException;
use Laminas\Filter\UpperCaseWords as UpperCaseWordsFilter;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use stdClass;

class UpperCaseWordsTest extends TestCase
{
    private UpperCaseWordsFilter $filter;

    public function setUp(): void
    {
        $this->filter = new UpperCaseWordsFilter();
    }

    /**
     * Ensures that the filter follows expected behavior
     */
    public function testBasic(): void
    {
        $filter         = $this->filter;
        $valuesExpected = [
            'string' => 'String',
            'aBc1@3' => 'Abc1@3',
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
        $valuesExpected = [
            '√º'      => '√º',
            '√±'      => '√±',
            '√º√±123' => '√º√±123',
        ];

        $filter = new UpperCaseWordsFilter(['encoding' => 'utf-8']);
        foreach ($valuesExpected as $input => $output) {
            self::assertSame($output, $filter($input));
        }
    }

    public function testFalseEncoding(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('is not supported');
        new UpperCaseWordsFilter(['encoding' => 'aaaaa']);
    }

    /**
     * @Laminas-9058
     */
    public function testCaseInsensitiveEncoding(): void
    {
        $valuesExpected = [
            '√º'      => '√º',
            '√±'      => '√±',
            '√º√±123' => '√º√±123',
        ];

        $filter = new UpperCaseWordsFilter(['encoding' => 'UTF-8']);
        foreach ($valuesExpected as $input => $output) {
            self::assertSame($output, $filter($input));
        }

        $filter = new UpperCaseWordsFilter(['encoding' => 'utf-8']);
        foreach ($valuesExpected as $input => $output) {
            self::assertSame($output, $filter($input));
        }

        $filter = new UpperCaseWordsFilter(['encoding' => 'UtF-8']);
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
            [123],
            [123.456],
            [
                [
                    'Upper CASE and lowerCase Words WRITTEN',
                    'This Should Stay The Same',
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
