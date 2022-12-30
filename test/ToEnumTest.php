<?php

declare(strict_types=1);

namespace LaminasTest\Filter;

use Laminas\Filter\Exception\InvalidArgumentException;
use Laminas\Filter\Exception\RuntimeException;
use Laminas\Filter\ToEnum;
use LaminasTest\Filter\TestAsset\TestIntBackedEnum;
use LaminasTest\Filter\TestAsset\TestStringBackedEnum;
use LaminasTest\Filter\TestAsset\TestUnitEnum;
use PHPUnit\Framework\TestCase;
use UnitEnum;

/**
 * @requires PHP 8.1
 */
class ToEnumTest extends TestCase
{
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
     * @param string|int $value
     */
    public function testCanFilterToEnum(string $enumClass, $value, UnitEnum $expectedFilteredValue): void
    {
        $filter = new ToEnum($enumClass);

        self::assertSame($expectedFilteredValue, $filter->filter($value));
    }

    /**
     * @dataProvider filterableValuesProvider
     * @param string|int $value
     */
    public function testCanFilterToEnumWithOptions(string $enumClass, $value, UnitEnum $expectedFilteredValue): void
    {
        $filter = new ToEnum(['enum' => $enumClass]);

        self::assertSame($expectedFilteredValue, $filter->filter($value));
    }

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
     * @param mixed $value
     */
    public function testFiltersToNull(string $enumClass, $value): void
    {
        $filter = new ToEnum($enumClass);

        self::assertNull($filter->filter($value));
    }

    public function testThrowsExceptionIfEnumNotSet(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('enum class not set');

        $filter = new ToEnum([]);

        $filter->filter('foo');
    }

    public function testThrowsExceptionIfEnumNotOfEnumType(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('enum is not of type enum');

        $filter = new ToEnum([]);

        $filter->setEnum('foo');
    }
}
