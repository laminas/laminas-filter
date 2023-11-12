<?php

declare(strict_types=1);

namespace Laminas\Filter;

use function is_scalar;

/**
 * @psalm-type Options = array{}
 * @extends AbstractFilter<Options>
 */
final class ToFloat extends AbstractFilter
{
    /**
     * Defined by Laminas\Filter\FilterInterface
     *
     * Returns (float) $value
     *
     * If the value provided is non-scalar, the value will remain unfiltered
     *
     * @psalm-return ($value is scalar ? float : mixed)
     */
    public function filter(mixed $value): mixed
    {
        if (! is_scalar($value)) {
            return $value;
        }

        return (float) $value;
    }
}
