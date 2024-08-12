<?php

declare(strict_types=1);

namespace Laminas\Filter;

/** @template TFilteredValue */
interface FilterInterface
{
    /**
     * Returns the result of filtering $value
     *
     * @template T
     * @param T $value
     * @return TFilteredValue|T
     * @throws Exception\RuntimeException If filtering $value is impossible.
     */
    public function filter(mixed $value): mixed;

    /**
     * Returns the result of filtering $value
     *
     * @template T
     * @param T $value
     * @return TFilteredValue|T
     * @throws Exception\RuntimeException If filtering $value is impossible.
     */
    public function __invoke(mixed $value): mixed;
}
