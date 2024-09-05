<?php

declare(strict_types=1);

namespace LaminasTest\Filter\TestAsset;

use Stringable;

final class StringableObject implements Stringable
{
    /** @param non-empty-string $value */
    public function __construct(
        private readonly string $value = 'Value',
    ) {
    }

    /** @return non-empty-string */
    public function __toString(): string
    {
        return $this->value;
    }
}
