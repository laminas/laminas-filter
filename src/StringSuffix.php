<?php

declare(strict_types=1);

namespace Laminas\Filter;

use function sprintf;

/**
 * @psalm-type Options = array{
 *     suffix?: non-empty-string|null,
 * }
 * @implements FilterInterface<string|array<array-key, string|mixed>>
 */
final class StringSuffix implements FilterInterface
{
    private readonly string $suffix;

    /** @param Options $options */
    public function __construct(array $options = [])
    {
        $this->suffix = $options['suffix'] ?? '';
    }

    public function filter(mixed $value): mixed
    {
        return ScalarOrArrayFilterCallback::applyRecursively(
            $value,
            fn (string $value): string => sprintf('%s%s', $value, $this->suffix),
        );
    }

    public function __invoke(mixed $value): mixed
    {
        return $this->filter($value);
    }
}
