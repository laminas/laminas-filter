<?php

declare(strict_types=1);

namespace Laminas\Filter\Word;

use Laminas\Filter\FilterInterface;

use function array_map;
use function is_array;
use function is_scalar;
use function str_replace;

/**
 * @psalm-type Options = array{
 *     separator?: string,
 *     ...
 * }
 * @template TOptions of Options
 * @implements FilterInterface<mixed>
 */
final class DashToSeparator implements FilterInterface
{
    protected string $separator = ' ';

    /**
     * @param Options|string $separator Space by default
     */
    public function __construct(string|array $separator = ' ')
    {
        if (is_array($separator) && isset($separator['separator'])) {
            $this->setSeparator($separator['separator']);

            return;
        }

        $this->setSeparator($separator);
    }

    public function __invoke(mixed $value): mixed
    {
        return $this->filter($value);
    }

    public function filter(mixed $value): mixed
    {
        if (! is_array($value)) {
            if (! is_scalar($value)) {
                return $value;
            }
            return $this->filterNormalizedValue((string) $value);
        }

        return $this->filterNormalizedValue(
            array_map(static fn($item) => is_scalar($item) ? (string) $item : $item, $value)
        );
    }

    /**
     * @param  string|string[] $value
     * @return string|string[]
     */
    private function filterNormalizedValue($value)
    {
        return str_replace('-', $this->separator, $value);
    }

    /** @return $this */
    public function setSeparator(string $separator): self
    {
        $this->separator = $separator;
        return $this;
    }

    public function getSeparator(): string
    {
        return $this->separator;
    }
}
