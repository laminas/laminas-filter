<?php

declare(strict_types=1);

namespace Laminas\Filter;

interface FilterInterface
{
    /**
     * Returns the result of filtering $value
     *
     * @param  mixed $value
     * @throws Exception\RuntimeException If filtering $value is impossible.
     * @return mixed
     */
    public function filter($value);
}
