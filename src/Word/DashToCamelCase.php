<?php

declare(strict_types=1);

namespace Laminas\Filter\Word;

use Laminas\Filter\FilterInterface;

/** @implements FilterInterface<string|array<array-key, string|mixed>> */
final class DashToCamelCase implements FilterInterface
{
    public function filter(mixed $value): mixed
    {
        return (new SeparatorToCamelCase(['separator' => '-']))->filter($value);
    }

    public function __invoke(mixed $value): mixed
    {
        return $this->filter($value);
    }
}
