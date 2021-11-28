<?php

declare(strict_types=1);

/**
 * @see       https://github.com/laminas/laminas-filter for the canonical source repository
 */

namespace LaminasTest\Filter;

use Laminas\Filter\BaseName as BaseNameFilter;
use PHPUnit\Framework\TestCase;
use stdClass;

class BaseNameTest extends TestCase
{
    /**
     * Ensures that the filter follows expected behavior
     *
     * @return void
     */
    public function testBasic()
    {
        $filter         = new BaseNameFilter();
        $valuesExpected = [
            '/path/to/filename'     => 'filename',
            '/path/to/filename.ext' => 'filename.ext',
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
        $filter = new BaseNameFilter();

        $this->assertEquals($input, $filter($input));
    }
}
