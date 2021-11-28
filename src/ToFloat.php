<?php

declare(strict_types=1);

namespace Laminas\Filter;

use function is_scalar;

class ToFloat extends AbstractFilter
{
    /**
     * Defined by Laminas\Filter\FilterInterface
     *
     * Returns (float) $value
     *
     * If the value provided is non-scalar, the value will remain unfiltered
     *
     * @param  mixed $value
     * @return float|mixed
     */
    public function filter($value)
    {
        if (! is_scalar($value)) {
            return $value;
        }

        return (float) $value;
    }
}
