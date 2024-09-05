<?php

declare(strict_types=1);

namespace LaminasTest\Filter;

use Laminas\Filter\ToFloat;
use LaminasTest\Filter\TestAsset\StringableObject;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class ToFloatTest extends TestCase
{
    /** @return array<string, array{0: mixed, 1: mixed}> */
    public static function filterableValuesProvider(): array
    {
        $object     = (object) ['foo' => 'bar'];
        $stringable = new StringableObject('Foo');

        return [
            'string word'  => ['string', 0.0],
            'string 1'     => ['1', 1.0],
            'string -1'    => ['-1', -1.0],
            'string 1.1'   => ['1.1', 1.1],
            'string -1.1'  => ['-1.1', -1.1],
            'string 0.9'   => ['0.9', 0.9],
            'string -0.9'  => ['-0.9', -0.9],
            'integer 1'    => [1, 1.0],
            'integer -1'   => [-1, -1.0],
            'true'         => [true, 1.0],
            'false'        => [false, 0.0],
            'float 1.1'    => [1.1, 1.1],
            'null'         => [null, null],
            'Object'       => [$object, $object],
            'Stringable'   => [$stringable, $stringable],
            'Empty String' => ['', 0.0],
            'Array'        => [[123.0, '123.0', 'foo'], [123.0, '123.0', 'foo']],
        ];
    }

    #[DataProvider('filterableValuesProvider')]
    public function testCanFilterScalarValuesAsExpected(mixed $input, mixed $expectedOutput): void
    {
        $filter = new ToFloat();
        self::assertSame($expectedOutput, $filter->filter($input));
        self::assertSame($expectedOutput, $filter->__invoke($input));
    }
}
