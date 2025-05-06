<?php

declare(strict_types=1);

namespace Laminas\Filter\Word;

use Laminas\Filter\FilterInterface;
use Laminas\Filter\ScalarOrArrayFilterCallback;

use function assert;
use function mb_strtoupper;
use function preg_quote;
use function preg_replace_callback;

/**
 * @psalm-type Options = array{
 *     separator?: string,
 * }
 * @implements FilterInterface<string|array<array-key, string|mixed>>
 */
final class SeparatorToCamelCase implements FilterInterface
{
    private readonly string $separator;

    /** @param Options $options */
    public function __construct(array $options = [])
    {
        $this->separator = $options['separator'] ?? ' ';
    }

    public function filter(mixed $value): mixed
    {
        // a unicode safe way of converting characters to \x00\x00 notation
        $pregQuotedSeparator = preg_quote($this->separator, '#');

        $patterns     = [
            '#(' . $pregQuotedSeparator . ')(\P{Z}{1})#u',
            '#(^\P{Z}{1})#u',
        ];
        $replacements = [
            static fn(array $matches): string => mb_strtoupper((string) $matches[2], 'UTF-8'),
            static fn(array $matches): string => mb_strtoupper((string) $matches[1], 'UTF-8'),
        ];

        return ScalarOrArrayFilterCallback::applyRecursively(
            $value,
            function (string $input) use ($patterns, $replacements): string {
                $filtered = $input;
                foreach ($patterns as $index => $pattern) {
                    $value = preg_replace_callback($pattern, $replacements[$index], $filtered);
                    assert($value !== null);

                    $filtered = $value;
                }
                return $filtered;
            }
        );
    }

    public function __invoke(mixed $value): mixed
    {
        return $this->filter($value);
    }
}
