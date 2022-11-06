<?php

declare(strict_types=1);

namespace LaminasTest\Filter;

use Laminas\Filter\Digits as DigitsFilter;
use PHPUnit\Framework\TestCase;
use stdClass;

use function preg_match;

class DigitsTest extends TestCase
{
    /**
     * Is PCRE is compiled with UTF-8 and Unicode support
     **/
    private bool $unicodeEnabled;

    /**
     * Creates a new Laminas_Filter_Digits object for each test method
     */
    public function setUp(): void
    {
        $this->unicodeEnabled = (bool) @preg_match('/\pL/u', 'a');
    }

    /**
     * Ensures that the filter follows expected behavior
     */
    public function testBasic(): void
    {
        $filter = new DigitsFilter();

        if ($this->unicodeEnabled) {
            // Filter for the value with mbstring
            /**
             * The first element of $valuesExpected contains multibyte digit characters.
             *   But , Laminas_Filter_Digits is expected to return only singlebyte digits.
             *
             * The second contains multibyte or singebyte space, and also alphabet.
             * The third  contains various multibyte characters.
             * The last contains only singlebyte digits.
             */
            $valuesExpected = [
                '1９2八3四８'     => '123',
                'Ｃ 4.5B　6'    => '456',
                '9壱8＠7．6，5＃4' => '987654',
                '789'         => '789',
            ];
        } else {
            // POSIX named classes are not supported, use alternative 0-9 match
            // Or filter for the value without mbstring
            $valuesExpected = [
                'abc123'  => '123',
                'abc 123' => '123',
                'abcxyz'  => '',
                'AZ@#4.3' => '43',
                '1.23'    => '123',
                '0x9f'    => '09',
            ];
        }

        foreach ($valuesExpected as $input => $output) {
            self::assertSame(
                $output,
                $result = $filter($input),
                "Expected '$input' to filter to '$output', but received '$result' instead"
            );
        }
    }

    /** @return list<array{0: mixed}> */
    public function returnUnfilteredDataProvider(): array
    {
        return [
            [null],
            [new stdClass()],
            [
                [
                    'abc123',
                    'abc 123',
                ],
            ],
            [true],
            [false],
        ];
    }

    /**
     * @dataProvider returnUnfilteredDataProvider
     */
    public function testReturnUnfiltered(mixed $input): void
    {
        $filter = new DigitsFilter();

        self::assertSame($input, $filter($input));
    }
}
