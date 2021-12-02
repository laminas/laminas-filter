<?php

declare(strict_types=1);

namespace Laminas\Filter;

use function dirname;
use function is_scalar;

class Dir extends AbstractFilter
{
    /**
     * Defined by Laminas\Filter\FilterInterface
     *
     * Returns dirname($value)
     *
     * @param  string $value
     * @return string
     */
    public function filter($value)
    {
        if (! is_scalar($value)) {
            return $value;
        }
        $value = (string) $value;

        return dirname($value);
    }
}
