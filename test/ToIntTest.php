<?php

declare(strict_types=1);

namespace LaminasTest\Filter;

use Laminas\Filter\ToInt as ToIntFilter;
use PHPUnit\Framework\TestCase;
use stdClass;

class ToIntTest extends TestCase
{
    /**
     * Ensures that the filter follows expected behavior
     *
     * @return void
     */
    public function testBasic()
    {
        $filter = new ToIntFilter();

        $valuesExpected = [
            'string' => 0,
            '1'      => 1,
            '-1'     => -1,
            '1.1'    => 1,
            '-1.1'   => -1,
            '0.9'    => 0,
            '-0.9'   => 0,
        ];
        foreach ($valuesExpected as $input => $output) {
            $this->assertEquals($output, $filter($input));
        }
    }

    public function returnUnfilteredDataProvider()
    {
        return [
            [null],
            [new stdClass()],
            [
                [
                    '1',
                    -1,
                ],
            ],
        ];
    }

    /**
     * @dataProvider returnUnfilteredDataProvider
     * @return void
     */
    public function testReturnUnfiltered($input)
    {
        $filter = new ToIntFilter();

        $this->assertEquals($input, $filter($input));
    }
}
