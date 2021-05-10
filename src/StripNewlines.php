<?php

namespace Laminas\Filter;

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
