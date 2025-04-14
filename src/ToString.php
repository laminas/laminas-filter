<?php

declare(strict_types=1);

namespace Laminas\Filter;

/** @implements FilterInterface<mixed> */
final class ToString implements FilterInterface
{
    /**
     * Returns (string) $value
     *
     * If the value provided is non-scalar, the value will remain unfiltered
     */
    public function filter(mixed $value): mixed
    {
        return ScalarOrArrayFilterCallback::applyRecursively(
            $value,
            fn (string $value): string => $value,
        );
    }

    public function __invoke(mixed $value): mixed
    {
        return $this->filter($value);
    }
}
