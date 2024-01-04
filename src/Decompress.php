<?php

declare(strict_types=1);

namespace Laminas\Filter;

use function is_string;

/**
 * Decompresses a given string
 *
 * @final
 */
class Decompress extends Compress
{
    /**
     * Use filter as functor
     *
     * Decompresses the content $value with the defined settings
     *
     * @param  mixed $value Content to decompress
     * @return mixed|string The decompressed content
     */
    public function __invoke($value)
    {
        return $this->filter($value);
    }

    /**
     * Defined by FilterInterface
     *
     * Decompresses the content $value with the defined settings
     *
     * @param  mixed $value Content to decompress
     * @return mixed|string The decompressed content
     */
    public function filter($value)
    {
        if (! is_string($value) && $value !== null) {
            return $value;
        }

        return $this->getAdapter()->decompress($value);
    }
}
