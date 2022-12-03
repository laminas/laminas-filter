<?php

declare(strict_types=1);

namespace Laminas\Filter;

use function basename;
use function is_scalar;

/**
 * @psalm-type Options = array{}
 * @extends AbstractFilter<Options>
 */
class BaseName extends AbstractFilter
{
    /**
     * Defined by Laminas\Filter\FilterInterface
     *
     * Returns basename($value).
     *
     * If the value provided is non-scalar, the value will remain unfiltered
     *
     * @param  mixed $value
     * @return string|mixed
     * @psalm-return ($value is scalar ? string : mixed)
     */
    public function filter($value)
    {
        if (! is_scalar($value)) {
            return $value;
        }

        return basename((string) $value);
    }
}
