<?php

declare(strict_types=1);

namespace Laminas\Filter;

use function str_replace;

/** @implements FilterInterface<string|array<array-key, string|mixed>> */
final class StripNewlines implements FilterInterface
{
    /**
     * Returns $value without newline control characters
     *
     * @inheritDoc
     */
    public function filter(mixed $value): mixed
    {
        return ScalarOrArrayFilterCallback::applyRecursively(
            $value,
            static fn(string $value): string => str_replace(["\n", "\r"], '', $value),
        );
    }

    public function __invoke(mixed $value): mixed
    {
        return $this->filter($value);
    }
}
