<?php

declare(strict_types=1);

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
     */
    public function testBasic(): void
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
            $this->assertSame($output, $filter($input));
        }
    }

    public function testArrayValues(): void
    {
        $filter   = new StripNewlinesFilter();
        $expected = [
            "Some text\nthat we have\r\nstuff in" => 'Some textthat we havestuff in',
            "Some text\n"                         => 'Some text',
        ];
        $this->assertSame(array_values($expected), $filter(array_keys($expected)));
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
     */
    public function testReturnUnfiltered($input): void
    {
        $filter = new StripNewlinesFilter();

        $this->assertSame($input, $filter($input));
    }
}
