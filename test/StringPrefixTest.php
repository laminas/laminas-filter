<?php

declare(strict_types=1);

namespace LaminasTest\Filter;

use Laminas\Filter\Exception\InvalidArgumentException;
use Laminas\Filter\StringPrefix as StringPrefixFilter;
use PHPUnit\Framework\TestCase;
use stdClass;

use function fopen;

class StringPrefixTest extends TestCase
{
    private StringPrefixFilter $filter;

    public function setUp(): void
    {
        $this->filter = new StringPrefixFilter();
    }

    /**
     * Ensures that the filter follows expected behavior
     */
    public function testBasic(): void
    {
        $filter = $this->filter;

        $prefix = 'ABC123';
        $filter->setPrefix($prefix);

        self::assertStringStartsWith($prefix, $filter('sample'));
    }

    public function testWithoutPrefix(): void
    {
        $filter = $this->filter;

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('expects a "prefix" option; none given');
        $filter('sample');
    }

    /**
     * @return array<string, array{0: mixed}>
     */
    public function invalidPrefixesDataProvider(): array
    {
        return [
            'int'                 => [1],
            'float'               => [1.00],
            'true'                => [true],
            'null'                => [null],
            'empty array'         => [[]],
            'resource'            => [fopen('php://memory', 'rb+')],
            'array with callable' => [
                static function (): void {
                },
            ],
            'object'              => [new stdClass()],
        ];
    }

    /**
     * @dataProvider invalidPrefixesDataProvider
     */
    public function testInvalidPrefixes(mixed $prefix): void
    {
        $filter = $this->filter;

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('expects "prefix" to be string');

        $filter->setPrefix($prefix);
        $filter('sample');
    }

    public function testNonScalarInput(): void
    {
        $filter = $this->filter;

        $prefix = 'ABC123';
        $filter->setPrefix($prefix);

        self::assertInstanceOf(stdClass::class, $filter(new stdClass()));
    }
}
