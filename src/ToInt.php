<?php

declare(strict_types=1);

namespace Laminas\Filter;

use function is_scalar;

/**
 * @psalm-type Options = array{}
 * @extends AbstractFilter<Options>
 */
class ToInt extends AbstractFilter
{
    /**
     * Defined by Laminas\Filter\FilterInterface
     *
     * Returns (int) $value
     *
     * If the value provided is non-scalar, the value will remain unfiltered
     *
     * @param  mixed $value
     * @return int|mixed
     * @psalm-return ($value is scalar ? int : mixed)
     */
    public function filter($value)
    {
        if (! is_scalar($value)) {
            return $value;
        }

        return (int) $value;
    }
}
