<?php

declare(strict_types=1);

namespace Laminas\Filter;

use function is_scalar;

/** @implements FilterInterface<float> */
final class ToFloat implements FilterInterface
{
    /**
     * Casts scalar values to float
     *
     * If the value provided is non-scalar, the value will remain unfiltered
     */
    public function filter(mixed $value): mixed
    {
        if (! is_scalar($value)) {
            return $value;
        }

        return (float) $value;
    }

    public function __invoke(mixed $value): mixed
    {
        return $this->filter($value);
    }
}
