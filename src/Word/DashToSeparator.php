<?php

declare(strict_types=1);

namespace Laminas\Filter\Word;

use Laminas\Filter\FilterInterface;
use Laminas\Filter\ScalarOrArrayFilterCallback;

use function str_replace;

/**
 * @psalm-type Options = array{
 *     separator?: string,
 *     ...
 * }
 * @template TOptions of Options
 * @implements FilterInterface<string|array<array-key, string|mixed>>
 */
final class DashToSeparator implements FilterInterface
{
    private readonly string $separator;

    /** @param Options $options */
    public function __construct(array $options = [])
    {
        $this->separator = $options['separator'] ?? ' ';
    }

    public function __invoke(mixed $value): mixed
    {
        return $this->filter($value);
    }

    public function filter(mixed $value): mixed
    {
        return ScalarOrArrayFilterCallback::applyRecursively(
            $value,
            fn (string $input): string => str_replace('-', $this->separator, $input)
        );
    }
}
