<?php

declare(strict_types=1);

namespace Laminas\Filter;

use function is_array;
use function is_scalar;
use function is_string;
use function str_replace;

class StripNewlines extends AbstractFilter
{
    /**
     * Defined by Laminas\Filter\FilterInterface
     *
     * Returns $value without newline control characters
     *
     * @param  mixed $value
     * @return mixed
     */
    public function filter($value)
    {
        if (! is_array($value)) {
            if (! is_scalar($value)) {
                return $value;
            }
            if (! is_string($value)) {
                $value = (string) $value;
            }
        }
        return str_replace(["\n", "\r"], '', $value);
    }
}
