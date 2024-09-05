<?php

declare(strict_types=1);

namespace LaminasTest\Filter;

use Laminas\Filter\Exception;
use Laminas\Filter\ToNull;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

use function gettype;
use function sprintf;
use function var_export;

/** @psalm-import-type TypeArgument from ToNull */
class ToNullTest extends TestCase
{
    public function testConstructorOptions(): void
    {
        $filter = new ToNull([
            'type' => ToNull::TYPE_INTEGER,
        ]);

        self::assertSame(null, $filter->__invoke(0));
        self::assertSame('0', $filter->__invoke('0'));
    }

    #[DataProvider('defaultTestProvider')]
    public function testDefault(mixed $value, mixed $expected): void
    {
        $filter = new ToNull();
        self::assertSame($expected, $filter->filter($value));
    }

    /**
     * @param int-mask-of<ToNull::TYPE_*> $type
     * @param list<array{0: mixed, 1: mixed}> $testData
     */
    #[DataProvider('typeTestProvider')]
    public function testTypes(int $type, array $testData): void
    {
        $filter = new ToNull(['type' => $type]);
        foreach ($testData as $data) {
            /** @psalm-suppress MixedAssignment */
            [$value, $expected] = $data;
            $message            = sprintf(
                '%s (%s) is not filtered as %s; type = %s',
                var_export($value, true),
                gettype($value),
                var_export($expected, true),
                (string) $type,
            );
            self::assertSame($expected, $filter->filter($value), $message);
        }
    }

    /**
     * @param list<TypeArgument> $typeData
     * @param list<array{0:mixed, 1: mixed}> $testData
     */
    #[DataProvider('combinedTypeTestProvider')]
    public function testCombinedTypes(array $typeData, array $testData): void
    {
        foreach ($typeData as $type) {
            $filter = new ToNull(['type' => $type]);
            foreach ($testData as $data) {
                /** @psalm-suppress MixedAssignment */
                [$value, $expected] = $data;
                $message            = sprintf(
                    '%s (%s) is not filtered as %s; type = %s',
                    var_export($value, true),
                    gettype($value),
                    var_export($expected, true),
                    var_export($type, true),
                );
                self::assertSame($expected, $filter->filter($value), $message);
            }
        }
    }

    /** @return list<array{0: mixed}> */
    public static function invalidTypeProvider(): array
    {
        return [
            [128],
            ['not-valid'],
            [['boolean', 'bad']],
            [[1, 2, 4, 128]],
        ];
    }

    #[DataProvider('invalidTypeProvider')]
    public function testUnresolvableTypeIsExceptional(mixed $type): void
    {
        $this->expectException(Exception\InvalidArgumentException::class);
        /** @psalm-suppress MixedArgumentTypeCoercion - Intentionally invalid argument */
        new ToNull(['type' => $type]);
    }

    /**
     * Ensures that providing a duplicate initializing type results in the expected type
     *
     * @param ToNull::TYPE_*|value-of<ToNull::CONSTANTS> $type
     */
    #[DataProvider('duplicateTypeProvider')]
    public function testDuplicateInitializationResultsInCorrectType(
        int|string $type,
        mixed $toNull,
        mixed $notNull,
    ): void {
        $filter = new ToNull(['type' => [$type, $type]]);

        self::assertNull($filter->filter($toNull));
        self::assertSame($notNull, $filter->filter($notNull));
    }

    /** @param ToNull::TYPE_*|value-of<ToNull::CONSTANTS> $type */
    #[DataProvider('duplicateTypeProvider')]
    public function testSingleStringOrIntType(
        int|string $type,
        mixed $toNull,
        mixed $notNull,
    ): void {
        $filter = new ToNull(['type' => $type]);

        self::assertNull($filter->filter($toNull));
        self::assertSame($notNull, $filter->filter($notNull));
    }

    /** @return list<array{0: int|string, 1: mixed, 2: mixed}> */
    public static function duplicateTypeProvider(): array
    {
        return [
            [ToNull::TYPE_BOOLEAN, false, true],
            [ToNull::TYPE_INTEGER, 0, 1],
            [ToNull::TYPE_EMPTY_ARRAY, [], ['foo']],
            [ToNull::TYPE_STRING, '', 'foo'],
            [ToNull::TYPE_ZERO_STRING, '0', 0],
            [ToNull::TYPE_FLOAT, 0.0, 1.0],
            [ToNull::TYPE_ALL, '', 'foo'],
            ['boolean', false, true],
            ['integer', 0, '0'],
            ['array', [], '0'],
            ['string', '', '0'],
            ['zero', '0', ''],
            ['float', 0.0, '0'],
            ['all', '0', 'foo'],
        ];
    }

    /** @return list<array{0: mixed, 1: mixed}> */
    public static function defaultTestProvider(): array
    {
        return [
            [null, null],
            [false, null],
            [true, true],
            [0, null],
            [1, 1],
            [0.0, null],
            [1.0, 1.0],
            ['', null],
            ['abc', 'abc'],
            ['0', null],
            ['1', '1'],
            [[], null],
            [[0], [0]],
        ];
    }

    /** @return list<array{0: int-mask-of<ToNull::TYPE_*>, 1: list<array{0: mixed, 1: mixed}>}> */
    public static function typeTestProvider(): array
    {
        return [
            [
                ToNull::TYPE_BOOLEAN,
                [
                    [null, null],
                    [false, null],
                    [true, true],
                    [0, 0],
                    [1, 1],
                    [0.0, 0.0],
                    [1.0, 1.0],
                    ['', ''],
                    ['abc', 'abc'],
                    ['0', '0'],
                    ['1', '1'],
                    [[], []],
                    [[0], [0]],
                ],
            ],
            [
                ToNull::TYPE_INTEGER,
                [
                    [null, null],
                    [false, false],
                    [true, true],
                    [0, null],
                    [1, 1],
                    [0.0, 0.0],
                    [1.0, 1.0],
                    ['', ''],
                    ['abc', 'abc'],
                    ['0', '0'],
                    ['1', '1'],
                    [[], []],
                    [[0], [0]],
                ],
            ],
            [
                ToNull::TYPE_EMPTY_ARRAY,
                [
                    [null, null],
                    [false, false],
                    [true, true],
                    [0, 0],
                    [1, 1],
                    [0.0, 0.0],
                    [1.0, 1.0],
                    ['', ''],
                    ['abc', 'abc'],
                    ['0', '0'],
                    ['1', '1'],
                    [[], null],
                    [[0], [0]],
                ],
            ],
            [
                ToNull::TYPE_STRING,
                [
                    [null, null],
                    [false, false],
                    [true, true],
                    [0, 0],
                    [1, 1],
                    [0.0, 0.0],
                    [1.0, 1.0],
                    ['', null],
                    ['abc', 'abc'],
                    ['0', '0'],
                    ['1', '1'],
                    [[], []],
                    [[0], [0]],
                ],
            ],
            [
                ToNull::TYPE_ZERO_STRING,
                [
                    [null, null],
                    [false, false],
                    [true, true],
                    [0, 0],
                    [1, 1],
                    [0.0, 0.0],
                    [1.0, 1.0],
                    ['', ''],
                    ['abc', 'abc'],
                    ['0', null],
                    ['1', '1'],
                    [[], []],
                    [[0], [0]],
                ],
            ],
            [
                ToNull::TYPE_FLOAT,
                [
                    [null, null],
                    [false, false],
                    [true, true],
                    [0, 0],
                    [1, 1],
                    [0.0, null],
                    [1.0, 1.0],
                    ['', ''],
                    ['abc', 'abc'],
                    ['0', '0'],
                    ['1', '1'],
                    [[], []],
                    [[0], [0]],
                ],
            ],
            [
                ToNull::TYPE_ALL,
                [
                    [null, null],
                    [false, null],
                    [true, true],
                    [0, null],
                    [1, 1],
                    [0.0, null],
                    [1.0, 1.0],
                    ['', null],
                    ['abc', 'abc'],
                    ['0', null],
                    ['1', '1'],
                    [[], null],
                    [[0], [0]],
                ],
            ],
        ];
    }

    /** @return list<array{0: list<TypeArgument>, 1: list<array{0:mixed, 1: mixed}>}> */
    public static function combinedTypeTestProvider(): array
    {
        return [
            [
                [
                    [
                        ToNull::TYPE_ZERO_STRING,
                        ToNull::TYPE_STRING,
                        ToNull::TYPE_BOOLEAN,
                    ],
                    [
                        'zero',
                        'string',
                        'boolean',
                    ],
                    ToNull::TYPE_ZERO_STRING | ToNull::TYPE_STRING | ToNull::TYPE_BOOLEAN,
                    ToNull::TYPE_ZERO_STRING + ToNull::TYPE_STRING + ToNull::TYPE_BOOLEAN,
                ],
                [
                    [null, null],
                    [false, null],
                    [true, true],
                    [0, 0],
                    [1, 1],
                    [0.0, 0.0],
                    [1.0, 1.0],
                    ['', null],
                    ['abc', 'abc'],
                    ['0', null],
                    ['1', '1'],
                    [[], []],
                    [[0], [0]],
                ],
            ],
        ];
    }
}
