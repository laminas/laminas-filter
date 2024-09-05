<?php

declare(strict_types=1);

namespace Laminas\Filter;

use function sprintf;

/**
 * @psalm-type Options = array{
 *     prefix?: non-empty-string|null,
 * }
 * @implements FilterInterface<string|array<array-key, string|mixed>>
 */
final class StringPrefix implements FilterInterface
{
    private readonly string $prefix;

    /** @param Options $options */
    public function __construct(array $options = [])
    {
        $this->prefix = $options['prefix'] ?? '';
    }

    public function filter(mixed $value): mixed
    {
        return ScalarOrArrayFilterCallback::applyRecursively(
            $value,
            fn (string $value): string => sprintf('%s%s', $this->prefix, $value),
        );
    }

    public function __invoke(mixed $value): mixed
    {
        return $this->filter($value);
    }
}
