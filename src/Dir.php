<?php

declare(strict_types=1);

namespace Laminas\Filter;

use function dirname;
use function is_string;

/**
 * @implements FilterInterface<string>
 */
final class Dir implements FilterInterface
{
    /**
     * Defined by Laminas\Filter\FilterInterface
     *
     * Returns dirname($value)
     */
    public function filter(mixed $value): mixed
    {
        if (! is_string($value) || $value === '') {
            return $value;
        }

        return dirname($value);
    }

    public function __invoke(mixed $value): mixed
    {
        return $this->filter($value);
    }
}
