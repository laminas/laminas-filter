<?php

declare(strict_types=1);

namespace Laminas\Filter;

use Laminas\Filter\Exception\InvalidArgumentException;

use function array_filter;
use function array_values;
use function is_array;
use function is_string;
use function preg_match;
use function preg_replace;
use function sprintf;
use function str_contains;

/**
 * @psalm-type Options = array{
 *     pattern: non-empty-string|list<non-empty-string>,
 *     replacement?: string|list<string>,
 * }
 * @implements FilterInterface<string|array<array-key, string|mixed>>
 */
final class PregReplace implements FilterInterface
{
    /** @var list<non-empty-string>|non-empty-string */
    private readonly array|string $pattern;
    /** @var list<string>|string */
    private readonly array|string $replacement;

    /**
     * Supported options are
     *     'pattern'     => matching pattern
     *     'replacement' => replace with this
     *
     * @param Options $options
     */
    public function __construct(array $options)
    {
        $this->pattern     = $this->validatePattern($options['pattern']);
        $this->replacement = $options['replacement'] ?? '';
    }

    public function filter(mixed $value): mixed
    {
        return ScalarOrArrayFilterCallback::applyRecursively(
            $value,
            fn (string $value): string => preg_replace($this->pattern, $this->replacement, $value),
        );
    }

    public function __invoke(mixed $value): mixed
    {
        return $this->filter($value);
    }

    /**
     * Validate pattern(s) and ensure they do not contain the "e" modifier
     *
     * @param string|list<string>|null $pattern
     * @return list<non-empty-string>|non-empty-string
     * @throws InvalidArgumentException
     */
    private function validatePattern(string|array|null $pattern): array|string
    {
        $test = array_values(array_filter(
            is_array($pattern) ? $pattern : [$pattern],
            static fn (mixed $value): bool => is_string($value) && $value !== '',
        ));

        if ($test === []) {
            throw new InvalidArgumentException(
                'The pattern option must be a non-empty string, or a list of non-empty strings',
            );
        }

        foreach ($test as $item) {
            if (! preg_match('/(?<modifier>[imsxeADSUXJu]+)$/', $item, $matches)) {
                continue;
            }

            if (str_contains($matches['modifier'], 'e')) {
                throw new InvalidArgumentException(sprintf(
                    'Pattern for a PregReplace filter may not contain the "e" pattern modifier; received "%s"',
                    $item,
                ));
            }
        }

        return $test;
    }
}
