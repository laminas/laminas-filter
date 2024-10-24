<?php

declare(strict_types=1);

namespace Laminas\Filter\Word;

use Laminas\Filter\FilterInterface;
use Laminas\Filter\ScalarOrArrayFilterCallback;

use function implode;
use function preg_split;
use function str_replace;

use const PREG_SPLIT_DELIM_CAPTURE;
use const PREG_SPLIT_NO_EMPTY;

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
        $pattern = <<<REGEXP
        /
        (
            (?:\p{Lu}\p{Ll}+) # Upper followed by lower
            |
            (?:\p{Lu}+(?!\p{Ll})) # Upper not followed by lower
            |
            (?:\p{N}+) # Runs of numbers
        )
        /ux
        REGEXP;

        return ScalarOrArrayFilterCallback::applyRecursively(
            $value,
            fn (string $input): string => implode(
                $this->separator,
                preg_split(
                    $pattern,
                    str_replace($this->separator, '', $input),
                    -1,
                    PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY,
                ),
            )
        );
    }
}
