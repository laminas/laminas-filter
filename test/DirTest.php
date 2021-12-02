<?php

declare(strict_types=1);

namespace LaminasTest\Filter;

use Laminas\Filter\Dir as DirFilter;
use PHPUnit\Framework\TestCase;
use stdClass;

class DirTest extends TestCase
{
    /**
     * Ensures that the filter follows expected behavior
     *
     * @return void
     */
    public function testBasic()
    {
        $filter         = new DirFilter();
        $valuesExpected = [
            'filename'              => '.',
            '/path/to/filename'     => '/path/to',
            '/path/to/filename.ext' => '/path/to',
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
                    '/path/to/filename',
                    '/path/to/filename.ext',
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
        $filter = new DirFilter();

        $this->assertEquals($input, $filter($input));
    }
}
