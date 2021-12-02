<?php

declare(strict_types=1);

namespace Laminas\Filter\Word;

use function is_array;
use function is_scalar;
use function str_replace;

class DashToSeparator extends AbstractSeparator
{
    /**
     * Defined by Laminas\Filter\Filter
     *
     * @param  string|array $value
     * @return string|array
     */
    public function filter($value)
    {
        if (! is_scalar($value) && ! is_array($value)) {
            return $value;
        }

        return str_replace('-', $this->separator, $value);
    }
}
