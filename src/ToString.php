<?php

declare(strict_types=1);

namespace Laminas\Filter;

use function is_scalar;

class ToString extends AbstractFilter
{
    /**
     * Returns (string) $value
     *
     * If the value provided is non-scalar, the value will remain unfiltered
     *
     * @param mixed $value
     * @psalm-return ($value is scalar ? string : mixed)
     */
    public function filter($value): mixed
    {
        if (! is_scalar($value)) {
            return $value;
        }

        return (string) $value;
    }
}
