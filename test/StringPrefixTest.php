<?php

declare(strict_types=1);

namespace LaminasTest\Filter;

use Laminas\Filter\StringPrefix;
use LaminasTest\Filter\TestAsset\StringableObject;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class StringPrefixTest extends TestCase
{
    /** @return array<string, array{0: non-empty-string|null, 1: mixed, 2: mixed}> */
    public static function basicDataProvider(): array
    {
        $object = (object) ['foo' => 'bar'];

        return [
            'Regular String' => ['PREFIX', 'value', 'PREFIXvalue'],
            'Integer'        => ['PREFIX', 1, 'PREFIX1'],
            'Float'          => ['PREFIX', 1.23, 'PREFIX1.23'],
            'True'           => ['PREFIX', true, 'PREFIX1'],
            'False'          => ['PREFIX', false, 'PREFIX'],
            'Null'           => ['PREFIX', null, null],
            'Empty String'   => ['PREFIX', '', 'PREFIX'],
            'Array'          => ['PREFIX', ['foo', 'bar'], ['PREFIXfoo', 'PREFIXbar']],
            'Nested Array'   => [
                'PREFIX',
                ['foo', 'bar' => ['baz' => 'bat']],
                ['PREFIXfoo', 'bar' => ['baz' => 'PREFIXbat']],
            ],
            'Stringable'     => ['PREFIX', new StringableObject('Foo'), 'PREFIXFoo'],
            'Object'         => ['PREFIX', $object, $object],
            'Empty Prefix'   => [null, 'String', 'String'],
        ];
    }

    /** @param non-empty-string|null $prefix */
    #[DataProvider('basicDataProvider')]
    public function testBasicBehaviour(?string $prefix, mixed $input, mixed $expect): void
    {
        $filter = new StringPrefix(['prefix' => $prefix]);

        self::assertSame($expect, $filter->filter($input));
        self::assertSame($expect, $filter->__invoke($input));
    }
}
