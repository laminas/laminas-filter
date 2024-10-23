<?php

declare(strict_types=1);

namespace Laminas\Filter\Word;

use Laminas\Filter\FilterInterface;
use Laminas\Filter\ScalarOrArrayFilterCallback;

use function preg_replace;

/**
 * @psalm-type Options = array{
 *     separator?: string,
 * }
 * @template TOptions of Options
 * @implements FilterInterface<string|array<array-key, string|mixed>>
 */
final class CamelCaseToSeparator implements FilterInterface
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
            fn (string $input): string => preg_replace(
                ['#(?<=(?:\p{Lu}))(\p{Lu}\p{Ll})#', '#(?<=(?:\p{Ll}|\p{Nd}))(\p{Lu})#'],
                [$this->separator . '\1', $this->separator . '\1'],
                $input
            )
        );
    }
}
