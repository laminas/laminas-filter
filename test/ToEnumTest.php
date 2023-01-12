<?php

declare(strict_types=1);

namespace LaminasTest\Filter;

use Laminas\Filter\Exception\RuntimeException;
use Laminas\Filter\ToEnum;
use LaminasTest\Filter\TestAsset\TestIntBackedEnum;
use LaminasTest\Filter\TestAsset\TestStringBackedEnum;
use LaminasTest\Filter\TestAsset\TestUnitEnum;
use PHPUnit\Framework\TestCase;
use UnitEnum;
use BackedEnum;

/**
 * @requires PHP 8.1
 */
class ToEnumTest extends TestCase
{
    /** @return array<string, array{0: class-string<UnitEnum>, 1: string|int, 2: UnitEnum|BackedEnum}> */
    public function filterableValuesProvider(): array
    {
        return [
            'unit enum'           => [TestUnitEnum::class, 'foo', TestUnitEnum::foo],
            'backed string enum'  => [TestStringBackedEnum::class, 'foo', TestStringBackedEnum::Foo],
            'backed integer enum' => [TestIntBackedEnum::class, 2, TestIntBackedEnum::Bar],
        ];
    }

    /**
     * @dataProvider filterableValuesProvider
     * @param class-string<UnitEnum> $enumClass
     */
    public function testCanFilterToEnum(string $enumClass, string|int $value, UnitEnum $expectedFilteredValue): void
    {
        $filter = new ToEnum($enumClass);

        self::assertSame($expectedFilteredValue, $filter->filter($value));
    }

    /**
     * @dataProvider filterableValuesProvider
     * @param class-string<UnitEnum> $enumClass
     */
    public function testCanFilterToEnumWithOptions(string $enumClass, string|int $value, UnitEnum $expectedFilteredValue): void
    {
        $filter = new ToEnum(['enum' => $enumClass]);

        self::assertSame($expectedFilteredValue, $filter->filter($value));
    }

    /** @return array<string, array{0: class-string<UnitEnum>, 1: mixed}> */
    public function unfilterableValuesProvider(): array
    {
        return [
            'array'               => [TestUnitEnum::class, []],
            'float'               => [TestUnitEnum::class, 1.1],
            'bool'                => [TestUnitEnum::class, false],
            'unit enum'           => [TestUnitEnum::class, 'baz'],
            'backed string enum'  => [TestStringBackedEnum::class, 'baz'],
            'backed integer enum' => [TestIntBackedEnum::class, 3],
        ];
    }

    /**
     * @dataProvider unfilterableValuesProvider
     * @param class-string<UnitEnum> $enumClass
     */
    public function testFiltersToNull(string $enumClass, mixed $value): void
    {
        $filter = new ToEnum($enumClass);

        self::assertEquals($value, $filter->filter($value));
    }

    public function testThrowsExceptionIfEnumNotSet(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('enum class not set');

        /**
         * @psalm-suppress InvalidArgument
         */
        $filter = new ToEnum([]);

        $filter->filter('foo');
    }
}
