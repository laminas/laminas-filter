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
     */
    public function testBasic(): void
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
            $this->assertSame($output, $filter($input));
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
     */
    public function testReturnUnfiltered($input): void
    {
        $filter = new ToIntFilter();

        $this->assertSame($input, $filter($input));
    }
}
