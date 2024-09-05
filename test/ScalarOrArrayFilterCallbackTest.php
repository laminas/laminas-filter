<?php

declare(strict_types=1);

namespace LaminasTest\Filter;

use Closure;
use Laminas\Filter\ScalarOrArrayFilterCallback;
use LaminasTest\Filter\TestAsset\StringableObject;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

use function strtolower;

class ScalarOrArrayFilterCallbackTest extends TestCase
{
    /** @var Closure(string): string */
    private Closure $filter;

    protected function setUp(): void
    {
        $this->filter = static fn (string $value): string => strtolower($value);
    }

    public static function scalarProvider(): array
    {
        return [
            'int'          => [100, '100'],
            'float'        => [1.23, '1.23'],
            'string'       => ['String', 'string'],
            'true'         => [true, '1'],
            'false'        => [false, ''],
            'empty string' => ['', ''],
            'null'         => [null, null],
            'stringable'   => [new StringableObject('Foo'), 'foo'],
        ];
    }

    #[DataProvider('scalarProvider')]
    public function testScalarValuesWillBeFilteredInPlace(mixed $input, mixed $expect): void
    {
        self::assertSame($expect, ScalarOrArrayFilterCallback::applyRecursively($input, $this->filter));
    }

    public function testAssociativeArrayArgument(): void
    {
        $input = [
            'int'        => 100,
            'string'     => 'String',
            'true'       => true,
            'empty'      => '',
            'null'       => null,
            'stringable' => new StringableObject('Foo'),
        ];

        $expect = [
            'int'        => '100',
            'string'     => 'string',
            'true'       => '1',
            'empty'      => '',
            'null'       => null,
            'stringable' => 'foo',
        ];

        self::assertSame($expect, ScalarOrArrayFilterCallback::applyRecursively($input, $this->filter));
    }

    public function testListArgument(): void
    {
        $input = [
            100,
            'String',
            true,
            '',
            null,
            new StringableObject('Foo'),
        ];

        $expect = [
            '100',
            'string',
            '1',
            '',
            null,
            'foo',
        ];

        self::assertSame($expect, ScalarOrArrayFilterCallback::applyRecursively($input, $this->filter));
    }

    public function testArraysAreRecursivelyProcessed(): void
    {
        $input = [
            'a' => 'A',
            'b' => [
                'c' => 'C',
                'd' => [
                    'e' => 'E',
                ],
            ],
        ];

        $expect = [
            'a' => 'a',
            'b' => [
                'c' => 'c',
                'd' => [
                    'e' => 'e',
                ],
            ],
        ];

        self::assertSame($expect, ScalarOrArrayFilterCallback::applyRecursively($input, $this->filter));
    }
}
