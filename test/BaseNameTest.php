<?php

declare(strict_types=1);

namespace LaminasTest\Filter;

use Laminas\Filter\BaseName as BaseNameFilter;
use PHPUnit\Framework\TestCase;
use stdClass;

class BaseNameTest extends TestCase
{
    /**
     * Ensures that the filter follows expected behavior
     */
    public function testBasic(): void
    {
        $filter         = new BaseNameFilter();
        $valuesExpected = [
            '/path/to/filename'     => 'filename',
            '/path/to/filename.ext' => 'filename.ext',
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
                    '/path/to/filename',
                    '/path/to/filename.ext',
                ],
            ],
        ];
    }

    /**
     * @dataProvider returnUnfilteredDataProvider
     */
    public function testReturnUnfiltered($input): void
    {
        $filter = new BaseNameFilter();

        $this->assertSame($input, $filter($input));
    }
}
