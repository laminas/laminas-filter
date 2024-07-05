<?php

declare(strict_types=1);

namespace LaminasTest\Filter;

use Generator;
use Laminas\Filter\Callback as CallbackFilter;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

use function PHPUnit\Framework\assertSame;

class CallbackTest extends TestCase
{
    /** @return Generator<string, array{0: callable(mixed): mixed}> */
    public static function callbackProvider(): Generator
    {
        yield 'Invokable Class' => [
            new class ()
            {
                public function __invoke(mixed $input): string
                {
                    assertSame('INPUT', $input);
                    return $input;
                }
            },
        ];

        yield 'Closure' => [
            function (mixed $input): string {
                assertSame('INPUT', $input);
                return $input;
            },
        ];

        yield 'Static method' => [
            [self::class, 'staticMethodTest'],
        ];

        $instance = new class
        {
            public function doStuff(mixed $input): string
            {
                assertSame('INPUT', $input);
                return $input;
            }
        };

        yield 'Instance method' => [
            [$instance, 'doStuff'],
        ];
    }

    /** @param callable(mixed): mixed $callback */
    #[DataProvider('callbackProvider')]
    public function testBasicBehaviour(callable $callback): void
    {
        $filter = new CallbackFilter($callback);
        self::assertSame('INPUT', $filter->filter('INPUT'));
    }

    public function testCallbackWithPHPBuiltIn(): void
    {
        $filter = new CallbackFilter('strrev');
        self::assertSame('!olleH', $filter('Hello!'));
    }

    /** @see self::callbackProvider() */
    public static function staticMethodTest(mixed $input): string
    {
        assertSame('INPUT', $input);
        return $input;
    }

    /** @return Generator<string, array{0: callable(mixed...): mixed}> */
    public static function callbackWithArgumentsProvider(): Generator
    {
        yield 'Invokable Class' => [
            new class ()
            {
                public function __invoke(mixed $input, int $a, int $b): int
                {
                    assertSame('INPUT', $input);

                    return $a + $b;
                }
            },
        ];

        yield 'Closure' => [
            function (mixed $input, int $a, int $b): int {
                assertSame('INPUT', $input);

                return $a + $b;
            },
        ];

        yield 'Static method' => [
            [self::class, 'staticMethodWithArgumentsTest'],
        ];

        $instance = new class
        {
            public function doStuff(mixed $input, int $a, int $b): int
            {
                assertSame('INPUT', $input);

                return $a + $b;
            }
        };

        yield 'Instance method' => [
            [$instance, 'doStuff'],
        ];
    }

    /** @see self::callbackWithArgumentsProvider() */
    public static function staticMethodWithArgumentsTest(mixed $input, int $a, int $b): int
    {
        assertSame('INPUT', $input);

        return $a + $b;
    }

    /** @param callable(mixed): mixed $callback */
    #[DataProvider('callbackWithArgumentsProvider')]
    public function testCallbackWithArguments(callable $callback): void
    {
        $filter = new CallbackFilter([
            'callback'        => $callback,
            'callback_params' => [
                'a' => 2,
                'b' => 2,
            ],
        ]);
        self::assertSame(4, $filter('INPUT'));
    }

    public function testAssocArrayTreatedAsNamedArguments(): void
    {
        /** @psalm-suppress InvalidArgument Psalm cannot declare optional variadics */
        $filter = new CallbackFilter([
            'callback'        => function (mixed $input, string $foo, string $bar): string {
                assertSame('INPUT', $input);

                return $foo . $bar;
            },
            'callback_params' => [
                'bar' => 'Baz',
                'foo' => 'Bing',
            ],
        ]);

        self::assertSame('BingBaz', $filter->filter('INPUT'));
    }

    public function testListArgumentsAreOrdered(): void
    {
        /** @psalm-suppress InvalidArgument Psalm cannot declare optional variadics */
        $filter = new CallbackFilter([
            'callback'        => function (mixed $input, string $foo, string $bar): string {
                assertSame('INPUT', $input);

                return $foo . $bar;
            },
            'callback_params' => [
                'Baz',
                'Bing',
            ],
        ]);

        self::assertSame('BazBing', $filter->filter('INPUT'));
    }
}
