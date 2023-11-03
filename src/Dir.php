<?php

declare(strict_types=1);

namespace Laminas\Filter;

use function dirname;
use function is_scalar;

/**
 * @psalm-type Options = array{}
 * @extends AbstractFilter<Options>
 * @final
 */
class Dir extends AbstractFilter
{
    /**
     * Defined by Laminas\Filter\FilterInterface
     *
     * Returns dirname($value)
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
        $value = (string) $value;

        return dirname($value);
    }
}
