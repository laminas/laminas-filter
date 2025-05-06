<?php

declare(strict_types=1);

namespace Laminas\Filter\Word;

use Laminas\Filter\FilterInterface;

/**
 * @psalm-type Options = array{
 *     separator?: string,
 * }
 * @template TOptions of Options
 * @implements FilterInterface<string|array<array-key, string|mixed>>
 */
final class UnderscoreToSeparator implements FilterInterface
{
    private readonly string $separator;

    /** @param Options $options */
    public function __construct(array $options = [])
    {
        $this->separator = $options['separator'] ?? ' ';
    }

    public function filter(mixed $value): mixed
    {
        return (new SeparatorToSeparator(
            ['search_separator' => '_', 'replacement_separator' => $this->separator]
        ))->filter($value);
    }

    public function __invoke(mixed $value): mixed
    {
        return $this->filter($value);
    }
}
