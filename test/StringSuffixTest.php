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
    private StringSuffixFilter $filter;

    public function setUp(): void
    {
        $this->filter = new StringSuffixFilter();
    }

    /**
     * Ensures that the filter follows expected behavior
     */
    public function testBasic(): void
    {
        $filter = $this->filter;

        $suffix = 'ABC123';
        $filter->setSuffix($suffix);

        self::assertStringEndsWith($suffix, $filter('sample'));
    }

    public function testWithoutSuffix(): void
    {
        $filter = $this->filter;

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('expects a "suffix" option; none given');
        $filter('sample');
    }

    /**
     * @return array<string, array{0: mixed}>
     */
    public function invalidSuffixesDataProvider(): array
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
     * @dataProvider invalidSuffixesDataProvider
     */
    public function testInvalidSuffixes(mixed $suffix): void
    {
        $filter = $this->filter;

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('expects "suffix" to be string');

        $filter->setSuffix($suffix);
        $filter('sample');
    }

    public function testNonScalarInput(): void
    {
        $filter = $this->filter;

        $suffix = 'ABC123';
        $filter->setSuffix($suffix);

        self::assertInstanceOf(stdClass::class, $filter(new stdClass()));
    }
}
