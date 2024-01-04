<?php

declare(strict_types=1);

namespace Laminas\Filter;

interface FilterInterface
{
    /**
     * Returns the result of filtering $value
     *
     * @throws Exception\RuntimeException If filtering $value is impossible.
     */
    public function filter(mixed $value): mixed;

    public function __invoke(mixed $value): mixed;
}
