<?php

declare(strict_types=1);

namespace LaminasTest\Filter\TestAsset;

use Stringable;

final class StringClass implements Stringable
{
    public function __construct(
        private readonly string $string,
    ) {
    }

    public function __toString(): string
    {
        return $this->string;
    }
}
