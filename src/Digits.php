<?php

declare(strict_types=1);

namespace Laminas\Filter;

use function is_float;
use function is_int;
use function is_string;
use function preg_replace;

/** @implements FilterInterface<numeric-string|''> */
final class Digits implements FilterInterface
{
    /**
     * Returns the string $value, removing all but digit characters
     *
     * If the value provided is not integer, float or string, the value will remain unfiltered
     *
     * @inheritDoc
     */
    public function filter(mixed $value): mixed
    {
        if (is_int($value)) {
            return (string) $value;
        }

        if (! is_float($value) && ! is_string($value)) {
            return $value;
        }

        return preg_replace('/[^[:digit:]]/', '', (string) $value);
    }

    public function __invoke(mixed $value): mixed
    {
        return $this->filter($value);
    }
}
