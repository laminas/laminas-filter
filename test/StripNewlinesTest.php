<?php

declare(strict_types=1);

/**
 * @see       https://github.com/laminas/laminas-filter for the canonical source repository
 */

namespace LaminasTest\Filter;

use Laminas\Filter\StripNewlines as StripNewlinesFilter;
use PHPUnit\Framework\TestCase;
use stdClass;

use function array_keys;
use function array_values;

class StripNewlinesTest extends TestCase
{
    /**
     * Ensures that the filter follows expected behavior
     *
     * @return void
     */
    public function testBasic()
    {
        $filter         = new StripNewlinesFilter();
        $valuesExpected = [
            ''                                    => '',
            "\n"                                  => '',
            "\r"                                  => '',
            "\r\n"                                => '',
            '\n'                                  => '\n',
            '\r'                                  => '\r',
            '\r\n'                                => '\r\n',
            "Some text\nthat we have\r\nstuff in" => 'Some textthat we havestuff in',
        ];
        foreach ($valuesExpected as $input => $output) {
            $this->assertEquals($output, $filter($input));
        }
    }

    /**
     * @return void
     */
    public function testArrayValues()
    {
        $filter   = new StripNewlinesFilter();
        $expected = [
            "Some text\nthat we have\r\nstuff in" => 'Some textthat we havestuff in',
            "Some text\n"                         => 'Some text',
        ];
        $this->assertEquals(array_values($expected), $filter(array_keys($expected)));
    }

    public function returnUnfilteredDataProvider()
    {
        return [
            [null],
            [new stdClass()],
        ];
    }

    /**
     * @dataProvider returnUnfilteredDataProvider
     * @return void
     */
    public function testReturnUnfiltered($input)
    {
        $filter = new StripNewlinesFilter();

        $this->assertEquals($input, $filter($input));
    }
}
