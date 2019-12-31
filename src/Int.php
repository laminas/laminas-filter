<?php

/**
 * @see       https://github.com/laminas/laminas-filter for the canonical source repository
 * @copyright https://github.com/laminas/laminas-filter/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-filter/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\Filter;

class Int extends AbstractFilter
{
    /**
     * Defined by Laminas\Filter\FilterInterface
     *
     * Returns (int) $value
     *
     * If the value provided is non-scalar, the value will remain unfiltered
     * and an E_USER_WARNING will be raised indicating it's unfilterable.
     *
     * @param  string $value
     * @return int|mixed
     */
    public function filter($value)
    {
        if (null === $value) {
            return null;
        }

        if (!is_scalar($value)) {
            trigger_error(
                sprintf(
                    '%s expects parameter to be scalar, "%s" given; cannot filter',
                    __METHOD__,
                    (is_object($value) ? get_class($value) : gettype($value))
                ),
                E_USER_WARNING
            );
            return $value;
        }

        return (int) ((string) $value);
    }
}
