<?php

declare(strict_types=1);

namespace Laminas\Filter;

use function is_array;
use function is_scalar;
use function str_replace;

class StripNewlines extends AbstractFilter
{
    /**
     * Defined by Laminas\Filter\FilterInterface
     *
     * Returns $value without newline control characters
     *
     * @param  string|array $value
     * @return string|array
     */
    public function filter($value)
    {
        if (! is_scalar($value) && ! is_array($value)) {
            return $value;
        }
        return str_replace(["\n", "\r"], '', $value);
    }
}
