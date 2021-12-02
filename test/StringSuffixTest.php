<?php

declare(strict_types=1);

namespace LaminasTest\Filter;

use Laminas\Filter\Exception\InvalidArgumentException;
use Laminas\Filter\StringSuffix as StringSuffixFilter;
use PHPUnit\Framework\TestCase;
use stdClass;

use function fopen;

class StringSuffixTest extends TestCase
{
    /** @var StringSuffixFilter */
    protected $filter;

    public function setUp(): void
    {
        $this->filter = new StringSuffixFilter();
    }

    /**
     * Ensures that the filter follows expected behavior
     *
     * @return void
     */
    public function testBasic()
    {
        $filter = $this->filter;

        $suffix = 'ABC123';
        $filter->setSuffix($suffix);

        $this->assertStringEndsWith($suffix, $filter('sample'));
    }

    public function testWithoutSuffix()
    {
        $filter = $this->filter;

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('expects a "suffix" option; none given');
        $filter('sample');
    }

    /**
     * @return array
     */
    public function invalidSuffixesDataProvider()
    {
        return [
            'int'                 => [1],
            'float'               => [1.00],
            'true'                => [true],
            'null'                => [null],
            'empty array'         => [[]],
            'resource'            => [fopen('php://memory', 'rb+')],
            'array with callable' => [
                function () {
                },
            ],
            'object'              => [new stdClass()],
        ];
    }

    /**
     * @dataProvider invalidSuffixesDataProvider
     * @param mixed $suffix
     */
    public function testInvalidSuffixes($suffix)
    {
        $filter = $this->filter;

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('expects "suffix" to be string');

        $filter->setSuffix($suffix);
        $filter('sample');
    }

    public function testNonScalarInput()
    {
        $filter = $this->filter;

        $suffix = 'ABC123';
        $filter->setSuffix($suffix);

        $this->assertInstanceOf(stdClass::class, $filter(new stdClass()));
    }
}
