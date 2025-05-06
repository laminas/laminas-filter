<?php

declare(strict_types=1);

namespace Laminas\Filter;

use Closure;
use Stringable;

use function array_map;
use function is_array;
use function is_scalar;

/**
 * This class is internal and as such is not subject to any backwards compatibility guarantees.
 *
 * @internal
 *
 * @psalm-internal \Laminas
 * @psalm-internal \LaminasTest
 */
final class ScalarOrArrayFilterCallback
{
    /**
     * Recursively applies a callback to an array of scalars or scalar input. Non-scalar values are skipped.
     *
     * @template T
     * @param T $value
     * @param Closure(string): string $callback
     * @return T|string|array<array-key, string|mixed>
     */
    public static function applyRecursively(mixed $value, Closure $callback): mixed
    {
        if (is_scalar($value) || $value instanceof Stringable) {
            return $callback((string) $value);
        }

        if (is_array($value)) {
            return array_map(
                static fn (mixed $value): mixed => self::applyRecursively($value, $callback),
                $value,
            );
        }

        return $value;
    }
}
