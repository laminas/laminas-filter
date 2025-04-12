<?php

declare(strict_types=1);

namespace LaminasTest\Filter;

use BackedEnum;
use Laminas\Filter\ToEnum;
use LaminasTest\Filter\ToEnum\BasicEnum;
use LaminasTest\Filter\ToEnum\IntEnum;
use LaminasTest\Filter\ToEnum\StringEnum;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use UnitEnum;

final class ToEnumTest extends TestCase
{
    /**
     * @return list<array{
     *     0: class-string<UnitEnum|BackedEnum>,
     *     1: mixed,
     *     2: mixed,
     * }>
     */
    public static function basicDataProvider(): array
    {
        return [
            [BasicEnum::class, 'Flip', BasicEnum::Flip],
            [BasicEnum::class, 'Flop', BasicEnum::Flop],
            [BasicEnum::class, 'fuzz', 'fuzz'],
            [BasicEnum::class, 'flip', 'flip'],
            [IntEnum::class, 1, IntEnum::One],
            [IntEnum::class, 2, IntEnum::Two],
            [IntEnum::class, '1', IntEnum::One],
            [IntEnum::class, '2', IntEnum::Two],
            [IntEnum::class, 3, 3],
            [IntEnum::class, '3', '3'],
            [StringEnum::class, 'Foo', StringEnum::Foo],
            [StringEnum::class, 'foo', StringEnum::Foo],
            [StringEnum::class, 'Bar', StringEnum::Bar],
            [StringEnum::class, 'bar', StringEnum::Bar],
            [StringEnum::class, 'baz', 'baz'],
            [StringEnum::class, 1, 1],
            [StringEnum::class, true, true],
            [StringEnum::class, ['Foo', 'Bar'], ['Foo', 'Bar']],
        ];
    }

    /** @param class-string<UnitEnum|BackedEnum> $enum */
    #[DataProvider('basicDataProvider')]
    public function testBasicBehaviour(string $enum, mixed $input, mixed $expect): void
    {
        $filter = new ToEnum([
            'enum' => $enum,
        ]);

        self::assertSame($expect, $filter->__invoke($input));
    }
}
