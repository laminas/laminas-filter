<?php

declare(strict_types=1);

namespace LaminasTest\Filter;

use Laminas\Filter\Digits as DigitsFilter;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

use function get_debug_type;
use function sprintf;

use const PHP_INT_MAX;

final class DigitsTest extends TestCase
{
    /** @return array<array-key, array{0: mixed, 1: mixed}> */
    public static function basicDataProvider(): array
    {
        $object = (object) ['foo'];

        return [
            'Mixed Unicode Numerics'        => ['1９2八3四８', '123'],
            'Unicode String with Numbers'   => ['Ｃ 4.5B　6', '456'],
            'Unicode String with Numbers 2' => ['9壱8＠7．6，5＃4', '987654'],
            'Numeric String'                => ['789', '789'],
            'ASCII Alnum 1'                 => ['abc123', '123'],
            'ASCII Alnum 2'                 => ['abc 123', '123'],
            'ASCII Alnum 3'                 => ['AZ@#4.3', '43'],
            'No Numbers'                    => ['abcxyz', ''],
            'Float String'                  => ['1.23', '123'],
            'Hex String'                    => ['0x9f', '09'],
            'Hex Int'                       => [0xff, '255'],
            'Boolean'                       => [true, true],
            'Null'                          => [null, null],
            'Array'                         => [['foo'], ['foo']],
            'Object'                        => [$object, $object],
            'Small Integer'                 => [123, '123'],
            'Big Integer'                   => [PHP_INT_MAX, (string) PHP_INT_MAX],
            'Float'                         => [3.141592653, '3141592653'],
        ];
    }

    #[DataProvider('basicDataProvider')]
    public function testBasic(mixed $input, mixed $expect): void
    {
        $filter = new DigitsFilter();

        /** @psalm-var mixed $result */
        $result = $filter->filter($input);

        self::assertSame(
            $expect,
            $result,
            sprintf(
                'Expected "%s" to filter to "%s", but received "%s" instead',
                get_debug_type($input),
                get_debug_type($expect),
                get_debug_type($result),
            ),
        );
    }

    #[DataProvider('basicDataProvider')]
    public function testInvokeProxiesToFilter(mixed $input): void
    {
        $filter = new DigitsFilter();

        self::assertSame(
            $filter->filter($input),
            $filter->__invoke($input),
        );
    }
}
