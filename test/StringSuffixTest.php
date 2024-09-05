<?php

declare(strict_types=1);

namespace LaminasTest\Filter;

use Laminas\Filter\StringSuffix;
use LaminasTest\Filter\TestAsset\StringableObject;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class StringSuffixTest extends TestCase
{
    /** @return array<string, array{0: non-empty-string|null, 1: mixed, 2: mixed}> */
    public static function basicDataProvider(): array
    {
        $object = (object) ['foo' => 'bar'];

        return [
            'Regular String' => ['SUFFIX', 'value', 'valueSUFFIX'],
            'Integer'        => ['SUFFIX', 1, '1SUFFIX'],
            'Float'          => ['SUFFIX', 1.23, '1.23SUFFIX'],
            'True'           => ['SUFFIX', true, '1SUFFIX'],
            'False'          => ['SUFFIX', false, 'SUFFIX'],
            'Null'           => ['SUFFIX', null, null],
            'Empty String'   => ['SUFFIX', '', 'SUFFIX'],
            'Array'          => ['SUFFIX', ['foo', 'bar'], ['fooSUFFIX', 'barSUFFIX']],
            'Nested Array'   => [
                'SUFFIX',
                ['foo', 'bar' => ['baz' => 'bat']],
                ['fooSUFFIX', 'bar' => ['baz' => 'batSUFFIX']],
            ],
            'Stringable'     => ['SUFFIX', new StringableObject('Foo'), 'FooSUFFIX'],
            'Object'         => ['SUFFIX', $object, $object],
            'Empty Suffix'   => [null, 'String', 'String'],
        ];
    }

    /** @param non-empty-string|null $suffix */
    #[DataProvider('basicDataProvider')]
    public function testBasicBehaviour(?string $suffix, mixed $input, mixed $expect): void
    {
        $filter = new StringSuffix(['suffix' => $suffix]);

        self::assertSame($expect, $filter->filter($input));
        self::assertSame($expect, $filter->__invoke($input));
    }
}
