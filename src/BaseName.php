<?php

declare(strict_types=1);

namespace Laminas\Filter;

use function basename;
use function is_string;

/**
 * @psalm-immutable
 * @implements FilterInterface<string>
 */
final class BaseName implements FilterInterface
{
    /**
     * Returns basename($value).
     *
     * If the value provided is non-string, the value will remain unfiltered
     */
    public function filter(mixed $value): mixed
    {
        if (! is_string($value)) {
            return $value;
        }

        return basename($value);
    }

    public function __invoke(mixed $value): mixed
    {
        return $this->filter($value);
    }
}
