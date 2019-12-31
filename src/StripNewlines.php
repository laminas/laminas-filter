<?php

/**
 * @see       https://github.com/laminas/laminas-filter for the canonical source repository
 * @copyright https://github.com/laminas/laminas-filter/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-filter/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\Filter;

class StripNewlines extends AbstractFilter
{

    /**
     * Defined by Laminas\Filter\FilterInterface
     *
     * Returns $value without newline control characters
     *
     * @param  string $value
     * @return string
     */
    public function filter($value)
    {
        return str_replace(array("\n", "\r"), '', $value);
    }
}
