<?php

declare(strict_types=1);

namespace LaminasTest\Filter;

use Laminas\Filter\Digits as DigitsFilter;
use PHPUnit\Framework\TestCase;
use stdClass;

use function extension_loaded;
use function preg_match;

class DigitsTest extends TestCase
{
    // @codingStandardsIgnoreStart
    /**
     * Is PCRE is compiled with UTF-8 and Unicode support
     *
     * @var mixed
     **/
    protected static $_unicodeEnabled;
    // @codingStandardsIgnoreEnd

    /**
     * Creates a new Laminas_Filter_Digits object for each test method
     */
    public function setUp(): void
    {
        if (null === static::$_unicodeEnabled) {
            static::$_unicodeEnabled = (bool) @preg_match('/\pL/u', 'a');
        }
    }

    /**
     * Ensures that the filter follows expected behavior
     *
     * @return void
     */
    public function testBasic()
    {
        $filter = new DigitsFilter();

        if (static::$_unicodeEnabled && extension_loaded('mbstring')) {
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
            $this->assertEquals(
                $output,
                $result = $filter($input),
                "Expected '$input' to filter to '$output', but received '$result' instead"
            );
        }
    }

    public function returnUnfilteredDataProvider()
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
     * @return void
     */
    public function testReturnUnfiltered($input)
    {
        $filter = new DigitsFilter();

        $this->assertSame($input, $filter($input));
    }
}
