<?php

namespace Laminas\Filter;

class BaseName extends AbstractFilter
{
    /**
     * Defined by Laminas\Filter\FilterInterface
     *
     * Returns basename($value).
     *
     * If the value provided is non-scalar, the value will remain unfiltered
     *
     * @param  string $value
     * @return string|mixed
     */
    public function filter($value)
    {
        if (! is_scalar($value)) {
            return $value;
        }
        $value = (string) $value;

        return basename($value);
    }
}
