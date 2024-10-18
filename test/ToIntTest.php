<?php

declare(strict_types=1);

namespace LaminasTest\Filter;

use Laminas\Filter\ToInt;
use LaminasTest\Filter\TestAsset\StringableObject;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

use const PHP_INT_MAX;

class ToIntTest extends TestCase
{
    /** @return array<string, array{0: mixed, 1: mixed}> */
    public static function basicDataProvider(): array
    {
        $object     = (object) ['foo' => 'bar'];
        $stringable = new StringableObject('Foo');

        return [
            'String'          => ['string', 0],
            'Digits'          => ['123', 123],
            'Float String'    => ['1.23', 1],
            'Float'           => [1.23, 1],
            'Int'             => [123, 123],
            'Array'           => [[123, '123', 'foo'], [123, '123', 'foo']],
            'Object'          => [$object, $object],
            'Stringable'      => [$stringable, $stringable],
            'Null'            => [null, null],
            'Empty String'    => ['', 0],
            'Negative Int'    => [-1, -1],
            'Negative String' => ['-1', -1],
            'Massive Number'  => ['9223372036854775807999', PHP_INT_MAX],
        ];
    }

    #[DataProvider('basicDataProvider')]
    public function testBasicBehaviour(mixed $input, mixed $expect): void
    {
        $filter = new ToInt();
        self::assertSame($expect, $filter->filter($input));
        self::assertSame($expect, $filter->__invoke($input));
    }
}
