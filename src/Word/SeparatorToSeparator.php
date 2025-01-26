<?php

declare(strict_types=1);

namespace Laminas\Filter\Word;

use Laminas\Filter\FilterInterface;
use Laminas\Filter\ScalarOrArrayFilterCallback;

use function assert;
use function is_string;
use function preg_quote;
use function preg_replace;

/**
 * @psalm-type Options = array{
 *     search_separator?: string,
 *     replacement_separator?: string,
 * }
 * @template TOptions of Options
 * @implements FilterInterface<string|array<array-key, string|mixed>>
 */
final readonly class SeparatorToSeparator implements FilterInterface
{
    private string $searchSeparator;
    private string $replacementSeparator;

    /** @param Options $options */
    public function __construct(array $options = [])
    {
        $this->searchSeparator      = $options['search_separator'] ?? ' ';
        $this->replacementSeparator = $options['replacement_separator'] ?? '-';
    }

    public function filter(mixed $value): mixed
    {
        return ScalarOrArrayFilterCallback::applyRecursively(
            $value,
            function (string $input): string {
                $result = preg_replace(
                    '#' . preg_quote($this->searchSeparator, '#') . '#',
                    $this->replacementSeparator,
                    $input
                );
                assert(is_string($result));

                return $result;
            },
        );
    }

    public function __invoke(mixed $value): mixed
    {
        return $this->filter($value);
    }
}
