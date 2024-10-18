<?php

declare(strict_types=1);

namespace LaminasTest\Filter;

use Laminas\Filter\Boolean;
use Laminas\Filter\Exception;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

use function gettype;
use function sprintf;
use function var_export;

/**
 * @psalm-import-type TypeOption from Boolean
 * @psalm-import-type OptionsArgument from Boolean
 */
class BooleanTest extends TestCase
{
    /**
     * @return list<array{0: OptionsArgument, 1: mixed, 2: mixed}>
     * @psalm-suppress PossiblyUnusedMethod
     */
    public static function integerProvider(): array
    {
        return [
            [['type' => Boolean::TYPE_INTEGER, 'casting' => false], 1, true],
            [['type' => Boolean::TYPE_INTEGER, 'casting' => false], 0, false],
            [['type' => Boolean::TYPE_INTEGER, 'casting' => true], 1, true],
            [['type' => Boolean::TYPE_INTEGER, 'casting' => true], 0, false],
            [['type' => Boolean::TYPE_INTEGER, 'casting' => true], 99, true],
            [['type' => Boolean::TYPE_INTEGER, 'casting' => false], 99, 99],
            [['type' => Boolean::TYPE_INTEGER, 'casting' => false], null, null],
            [['type' => [Boolean::TYPE_INTEGER], 'casting' => false], 1, true],
            [['type' => 'integer', 'casting' => false], 1, true],
            [['type' => ['integer'], 'casting' => false], 1, true],
        ];
    }

    /**
     * @return list<array{0: OptionsArgument, 1: mixed, 2: mixed}>
     * @psalm-suppress PossiblyUnusedMethod
     */
    public static function floatProvider(): array
    {
        return [
            [['type' => Boolean::TYPE_FLOAT, 'casting' => false], 1.0, true],
            [['type' => Boolean::TYPE_FLOAT, 'casting' => false], 0.0, false],
            [['type' => Boolean::TYPE_FLOAT, 'casting' => true], 1.0, true],
            [['type' => Boolean::TYPE_FLOAT, 'casting' => true], 0.0, false],
            [['type' => Boolean::TYPE_FLOAT, 'casting' => true], 99.9, true],
            [['type' => Boolean::TYPE_FLOAT, 'casting' => false], 99.9, 99.9],
            [['type' => Boolean::TYPE_FLOAT, 'casting' => false], null, null],
            [['type' => [Boolean::TYPE_FLOAT], 'casting' => false], 1.0, true],
            [['type' => 'float', 'casting' => false], 1.0, true],
            [['type' => ['float'], 'casting' => false], 1.0, true],
        ];
    }

    /**
     * @return list<array{0: OptionsArgument, 1: mixed, 2: mixed}>
     * @psalm-suppress PossiblyUnusedMethod
     */
    public static function booleanProvider(): array
    {
        return [
            [['type' => Boolean::TYPE_BOOLEAN, 'casting' => false], true, true],
            [['type' => Boolean::TYPE_BOOLEAN, 'casting' => false], false, false],
            [['type' => Boolean::TYPE_BOOLEAN, 'casting' => false], 'foo', 'foo'],
            [['type' => Boolean::TYPE_BOOLEAN, 'casting' => false], 'true', 'true'],
            [['type' => Boolean::TYPE_BOOLEAN, 'casting' => false], 1, 1],
            [['type' => Boolean::TYPE_BOOLEAN, 'casting' => false], 0, 0],
            [['type' => Boolean::TYPE_BOOLEAN, 'casting' => false], [], []],
            [['type' => Boolean::TYPE_BOOLEAN, 'casting' => true], true, true],
            [['type' => Boolean::TYPE_BOOLEAN, 'casting' => true], false, false],
            [['type' => Boolean::TYPE_BOOLEAN, 'casting' => true], 'foo', true],
            [['type' => Boolean::TYPE_BOOLEAN, 'casting' => true], 'true', true],
            [['type' => Boolean::TYPE_BOOLEAN, 'casting' => true], 1, true],
            [['type' => Boolean::TYPE_BOOLEAN, 'casting' => true], 0, true],
            [['type' => Boolean::TYPE_BOOLEAN, 'casting' => true], [], true],
            [['type' => [Boolean::TYPE_BOOLEAN], 'casting' => false], true, true],
            [['type' => ['boolean'], 'casting' => false], true, true],
            [['type' => 'boolean', 'casting' => false], true, true],
        ];
    }

    /**
     * @return list<array{0: OptionsArgument, 1: mixed, 2: mixed}>
     * @psalm-suppress PossiblyUnusedMethod
     */
    public static function stringProvider(): array
    {
        return [
            [['type' => Boolean::TYPE_STRING, 'casting' => false], 'foo', 'foo'],
            [['type' => Boolean::TYPE_STRING, 'casting' => false], '', false],
            [['type' => Boolean::TYPE_STRING, 'casting' => true], 'foo', true],
            [['type' => Boolean::TYPE_STRING, 'casting' => true], '', false],
            [['type' => Boolean::TYPE_STRING, 'casting' => true], ' ', true],
            [['type' => Boolean::TYPE_STRING, 'casting' => true], "\t", true],
            [['type' => Boolean::TYPE_STRING, 'casting' => true], "\n", true],
            [['type' => [Boolean::TYPE_STRING], 'casting' => false], 'foo', 'foo'],
            [['type' => ['string'], 'casting' => false], 'foo', 'foo'],
            [['type' => 'string', 'casting' => false], 'foo', 'foo'],
        ];
    }

    /**
     * @return list<array{0: OptionsArgument, 1: mixed, 2: mixed}>
     * @psalm-suppress PossiblyUnusedMethod
     */
    public static function falseStringProvider(): array
    {
        return [
            [['type' => Boolean::TYPE_FALSE_STRING, 'casting' => false], 'true', true],
            [['type' => Boolean::TYPE_FALSE_STRING, 'casting' => false], 'false', false],
            [['type' => Boolean::TYPE_FALSE_STRING, 'casting' => true], 'true', true],
            [['type' => Boolean::TYPE_FALSE_STRING, 'casting' => true], 'false', false],
            [['type' => [Boolean::TYPE_FALSE_STRING], 'casting' => false], 'false', false],
            [['type' => ['false'], 'casting' => false], 'false', false],
            [['type' => 'false', 'casting' => false], 'false', false],
        ];
    }

    /**
     * @return list<array{0: OptionsArgument, 1: mixed, 2: mixed}>
     * @psalm-suppress PossiblyUnusedMethod
     */
    public static function nullProvider(): array
    {
        return [
            [['type' => Boolean::TYPE_NULL, 'casting' => false], null, false],
            [['type' => Boolean::TYPE_NULL, 'casting' => true], null, false],
            [['type' => Boolean::TYPE_NULL, 'casting' => true], 'false', true],
            [['type' => [Boolean::TYPE_NULL], 'casting' => false], 'false', 'false'],
            [['type' => ['null'], 'casting' => false], null, false],
            [['type' => 'null', 'casting' => false], null, false],
        ];
    }

    /**
     * @return list<array{0: OptionsArgument, 1: mixed, 2: mixed}>
     * @psalm-suppress PossiblyUnusedMethod
     */
    public static function zeroStringProvider(): array
    {
        return [
            [['type' => Boolean::TYPE_ZERO_STRING, 'casting' => false], '0', false],
            [['type' => Boolean::TYPE_ZERO_STRING, 'casting' => false], '1', true],
            [['type' => Boolean::TYPE_ZERO_STRING, 'casting' => true], '0', false],
            [['type' => Boolean::TYPE_ZERO_STRING, 'casting' => true], '1', true],
            [['type' => [Boolean::TYPE_ZERO_STRING], 'casting' => false], '0', false],
            [['type' => ['zero'], 'casting' => false], '0', false],
            [['type' => 'zero', 'casting' => false], '0', false],
        ];
    }

    /**
     * @return list<array{0: OptionsArgument, 1: mixed, 2: mixed}>
     * @psalm-suppress PossiblyUnusedMethod
     */
    public static function emptyArrayProvider(): array
    {
        return [
            [['type' => Boolean::TYPE_EMPTY_ARRAY, 'casting' => false], [], false],
            [['type' => Boolean::TYPE_EMPTY_ARRAY, 'casting' => false], ['foo'], ['foo']],
            [['type' => Boolean::TYPE_EMPTY_ARRAY, 'casting' => true], [], false],
            [['type' => Boolean::TYPE_EMPTY_ARRAY, 'casting' => true], ['foo'], true],
            [['type' => [Boolean::TYPE_EMPTY_ARRAY], 'casting' => false], [], false],
            [['type' => ['array'], 'casting' => false], [], false],
            [['type' => 'array', 'casting' => false], [], false],
        ];
    }

    /** @param OptionsArgument $options */
    #[DataProvider('integerProvider')]
    #[DataProvider('floatProvider')]
    #[DataProvider('booleanProvider')]
    #[DataProvider('stringProvider')]
    #[DataProvider('falseStringProvider')]
    #[DataProvider('nullProvider')]
    #[DataProvider('zeroStringProvider')]
    #[DataProvider('emptyArrayProvider')]
    public function testIndividualTypes(array $options, mixed $input, mixed $expect): void
    {
        $filter = new Boolean($options);

        /** @psalm-var mixed $result */
        $result = $filter->filter($input);

        $message = sprintf(
            'Expected (%s) %s to be filtered to (%s) %s',
            gettype($input),
            var_export($input, true),
            gettype($expect),
            var_export($expect, true),
        );

        self::assertSame($expect, $result, $message);
        self::assertSame($expect, $filter->__invoke($input));
    }

    #[DataProvider('defaultTestProvider')]
    public function testDefault(mixed $value, bool $expected): void
    {
        $filter = new Boolean();
        self::assertSame($expected, $filter->filter($value));
    }

    /**
     * @param int-mask-of<Boolean::TYPE_*> $type
     * @param list<array{0: mixed, 1: mixed}> $testData
     */
    #[DataProvider('typeTestProvider')]
    public function testTypes(int $type, array $testData): void
    {
        $filter = new Boolean(['type' => $type]);
        foreach ($testData as $data) {
            /**
             * @var mixed $value
             * @var mixed $expected
             */
            [$value, $expected] = $data;
            $message            = sprintf(
                '%s (%s) is not filtered as %s; type = %s',
                var_export($value, true),
                gettype($value),
                var_export($expected, true),
                $type,
            );
            self::assertSame($expected, $filter->filter($value), $message);
        }
    }

    /**
     * @param list<TypeOption> $typeData
     * @param list<array{0:mixed, 1:mixed}> $testData
     */
    #[DataProvider('combinedTypeTestProvider')]
    public function testCombinedTypes(array $typeData, array $testData): void
    {
        foreach ($typeData as $type) {
            $filter = new Boolean(['type' => $type]);
            foreach ($testData as $data) {
                /**
                 * @psalm-var mixed $value
                 * @psalm-var mixed $expected
                 */
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

    public function testLocalized(): void
    {
        $filter = new Boolean([
            'type'         => Boolean::TYPE_LOCALIZED,
            'translations' => [
                'yes' => true,
                'y'   => true,
                'no'  => false,
                'n'   => false,
                'yay' => true,
                'nay' => false,
            ],
        ]);

        self::assertTrue($filter->filter('yes'));
        self::assertTrue($filter->filter('yay'));
        self::assertFalse($filter->filter('n'));
        self::assertFalse($filter->filter('nay'));
    }

    public function testInvalidType(): void
    {
        $this->expectException(Exception\InvalidArgumentException::class);
        $this->expectExceptionMessage('Unknown type value');

        /** @psalm-suppress InvalidArgument */
        new Boolean(['type' => 'foo']);
    }

    /**
     * @return list<array{0: mixed, 1: bool}>
     * @psalm-suppress PossiblyUnusedMethod
     */
    public static function defaultTestProvider(): array
    {
        return [
            [false, false],
            [true, true],
            [0, false],
            [1, true],
            [0.0, false],
            [1.0, true],
            ['', false],
            ['abc', true],
            ['0', false],
            ['1', true],
            [[], false],
            [[0], true],
            [null, false],
            ['false', true],
            ['true', true],
            ['no', true],
            ['yes', true],
        ];
    }

    /**
     * @return list<array{0: int-mask-of<Boolean::TYPE_*>, 1: list<array{0: mixed, 1: mixed}>}>
     * @psalm-suppress PossiblyUnusedMethod
     */
    public static function typeTestProvider(): array
    {
        return [
            [
                Boolean::TYPE_BOOLEAN,
                [
                    [false, false],
                    [true, true],
                    [0, true],
                    [1, true],
                    [0.0, true],
                    [1.0, true],
                    ['', true],
                    ['abc', true],
                    ['0', true],
                    ['1', true],
                    [[], true],
                    [[0], true],
                    [null, true],
                    ['false', true],
                    ['true', true],
                    ['no', true],
                    ['yes', true],
                ],
            ],
            [
                Boolean::TYPE_INTEGER,
                [
                    [false, true],
                    [true, true],
                    [0, false],
                    [1, true],
                    [0.0, true],
                    [1.0, true],
                    ['', true],
                    ['abc', true],
                    ['0', true],
                    ['1', true],
                    [[], true],
                    [[0], true],
                    [null, true],
                    ['false', true],
                    ['true', true],
                    ['no', true],
                    ['yes', true],
                ],
            ],
            [
                Boolean::TYPE_FLOAT,
                [
                    [false, true],
                    [true, true],
                    [0, true],
                    [1, true],
                    [0.0, false],
                    [1.0, true],
                    ['', true],
                    ['abc', true],
                    ['0', true],
                    ['1', true],
                    [[], true],
                    [[0], true],
                    [null, true],
                    ['false', true],
                    ['true', true],
                    ['no', true],
                    ['yes', true],
                ],
            ],
            [
                Boolean::TYPE_STRING,
                [
                    [false, true],
                    [true, true],
                    [0, true],
                    [1, true],
                    [0.0, true],
                    [1.0, true],
                    ['', false],
                    ['abc', true],
                    ['0', true],
                    ['1', true],
                    [[], true],
                    [[0], true],
                    [null, true],
                    ['false', true],
                    ['true', true],
                    ['no', true],
                    ['yes', true],
                ],
            ],
            [
                Boolean::TYPE_ZERO_STRING,
                [
                    [false, true],
                    [true, true],
                    [0, true],
                    [1, true],
                    [0.0, true],
                    [1.0, true],
                    ['', true],
                    ['abc', true],
                    ['0', false],
                    ['1', true],
                    [[], true],
                    [[0], true],
                    [null, true],
                    ['false', true],
                    ['true', true],
                    ['no', true],
                    ['yes', true],
                ],
            ],
            [
                Boolean::TYPE_EMPTY_ARRAY,
                [
                    [false, true],
                    [true, true],
                    [0, true],
                    [1, true],
                    [0.0, true],
                    [1.0, true],
                    ['', true],
                    ['abc', true],
                    ['0', true],
                    ['1', true],
                    [[], false],
                    [[0], true],
                    [null, true],
                    ['false', true],
                    ['true', true],
                    ['no', true],
                    ['yes', true],
                ],
            ],
            [
                Boolean::TYPE_NULL,
                [
                    [false, true],
                    [true, true],
                    [0, true],
                    [1, true],
                    [0.0, true],
                    [1.0, true],
                    ['', true],
                    ['abc', true],
                    ['0', true],
                    ['1', true],
                    [[], true],
                    [[0], true],
                    [null, false],
                    ['false', true],
                    ['true', true],
                    ['no', true],
                    ['yes', true],
                ],
            ],
            [
                Boolean::TYPE_PHP,
                [
                    [false, false],
                    [true, true],
                    [0, false],
                    [1, true],
                    [0.0, false],
                    [1.0, true],
                    ['', false],
                    ['abc', true],
                    ['0', false],
                    ['1', true],
                    [[], false],
                    [[0], true],
                    [null, false],
                    ['false', true],
                    ['true', true],
                    ['no', true],
                    ['yes', true],
                ],
            ],
            [
                Boolean::TYPE_FALSE_STRING,
                [
                    [false, true],
                    [true, true],
                    [0, true],
                    [1, true],
                    [0.0, true],
                    [1.0, true],
                    ['', true],
                    ['abc', true],
                    ['0', true],
                    ['1', true],
                    [[], true],
                    [[0], true],
                    [null, true],
                    ['false', false],
                    ['true', true],
                    ['no', true],
                    ['yes', true],
                ],
            ],
            // default behaviour with no translations provided
            // all values filtered as true
            [
                Boolean::TYPE_LOCALIZED,
                [
                    [false, true],
                    [true, true],
                    [0, true],
                    [1, true],
                    [0.0, true],
                    [1.0, true],
                    ['', true],
                    ['abc', true],
                    ['0', true],
                    ['1', true],
                    [[], true],
                    [[0], true],
                    [null, true],
                    ['false', true],
                    ['true', true],
                    ['no', true],
                    ['yes', true],
                ],
            ],
            [
                Boolean::TYPE_ALL,
                [
                    [false, false],
                    [true, true],
                    [0, false],
                    [1, true],
                    [0.0, false],
                    [1.0, true],
                    ['', false],
                    ['abc', true],
                    ['0', false],
                    ['1', true],
                    [[], false],
                    [[0], true],
                    [null, false],
                    ['false', false],
                    ['true', true],
                    ['no', true],
                    ['yes', true],
                ],
            ],
        ];
    }

    /**
     * @return list<array{0: list<TypeOption>, 1: list<array{0:mixed, 1:mixed}>}>
     * @psalm-suppress PossiblyUnusedMethod
     */
    public static function combinedTypeTestProvider(): array
    {
        return [
            [
                [
                    [
                        Boolean::TYPE_ZERO_STRING,
                        Boolean::TYPE_STRING,
                        Boolean::TYPE_BOOLEAN,
                    ],
                    [
                        'zero',
                        'string',
                        'boolean',
                    ],
                    Boolean::TYPE_ZERO_STRING | Boolean::TYPE_STRING | Boolean::TYPE_BOOLEAN,
                    Boolean::TYPE_ZERO_STRING + Boolean::TYPE_STRING + Boolean::TYPE_BOOLEAN,
                ],
                [
                    [false, false],
                    [true, true],
                    [0, true],
                    [1, true],
                    [0.0, true],
                    [1.0, true],
                    ['', false],
                    ['abc', true],
                    ['0', false],
                    ['1', true],
                    [[], true],
                    [[0], true],
                    [null, true],
                    ['false', true],
                    ['true', true],
                    ['no', true],
                    ['yes', true],
                ],
            ],
        ];
    }

    /**
     * @return list<array{0: OptionsArgument}>
     * @psalm-suppress PossiblyUnusedMethod
     */
    public static function duplicateProvider(): array
    {
        return [
            [['type' => [Boolean::TYPE_BOOLEAN, Boolean::TYPE_BOOLEAN], 'casting' => false]],
            [['type' => ['boolean', Boolean::TYPE_BOOLEAN], 'casting' => false]],
            [['type' => ['boolean', 'boolean'], 'casting' => false]],
        ];
    }

    /**
     * Ensures that if a type is specified more than once, we get the expected type, not something else.
     * https://github.com/zendframework/zend-filter/issues/48
     *
     * @param OptionsArgument $options
     */
    #[DataProvider('duplicateProvider')]
    public function testDuplicateTypesWorkProperly(array $options): void
    {
        $filter = new Boolean($options);
        self::assertFalse($filter->filter(false));
        self::assertTrue($filter->filter(true));
    }
}
