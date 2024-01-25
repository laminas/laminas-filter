<?php

declare(strict_types=1);

namespace LaminasTest\Filter\TestAsset;

use Laminas\Filter\FilterInterface;

use function is_string;
use function preg_replace;

/** @implements FilterInterface<string> */
class StripUpperCase implements FilterInterface
{
    /** @inheritDoc */
    public function filter(mixed $value): mixed
    {
        if (! is_string($value)) {
            return $value;
        }

        return preg_replace('/[A-Z]/', '', $value);
    }

    /** @inheritDoc */
    public function __invoke(mixed $value): mixed
    {
        return $this->filter($value);
    }
}
