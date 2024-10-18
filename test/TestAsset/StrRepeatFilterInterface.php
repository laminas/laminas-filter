<?php

declare(strict_types=1);

namespace LaminasTest\Filter\TestAsset;

use Laminas\Filter\FilterInterface;

use function str_repeat;

/** @implements FilterInterface<string> */
class StrRepeatFilterInterface implements FilterInterface
{
    public function filter(mixed $value): mixed
    {
        return str_repeat((string) $value, 2);
    }

    public function __invoke(mixed $value): mixed
    {
        return $this->filter($value);
    }
}
