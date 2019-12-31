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
     * @param  string $value
     * @return int
     */
    public function filter($value)
    {
        return (int) ((string) $value);
    }
}
