<?php

declare(strict_types=1);

namespace Laminas\Filter;

use function array_reduce;
use function count;
use function is_array;
use function is_iterable;
use function ksort;
use function sprintf;
use function vsprintf;

/**
 * @psalm-type Options = array{
 *     null_on_empty?: bool,
 *     null_on_all_empty?: bool,
 *     ...
 * }
 * @psalm-type InputArray = array<string, string>
 * @template TOptions of Options
 * @template-extends AbstractFilter<TOptions>
 * @template TInput of array<array-key, numeric>
 */
abstract class AbstractDateDropdown extends AbstractFilter
{
    /**
     * If true, the filter will return null if any date field is empty
     */
    protected bool $nullOnEmpty = false;

    /**
     * If true, the filter will return null if all date fields are empty
     */
    protected bool $nullOnAllEmpty = false;

    /**
     * Sprintf format string to use for formatting the date, fields will be used in alphabetical order.
     */
    protected string $format = '';

    protected int $expectedInputs = 0;

    /**
     * @param mixed $options If array or Traversable, passes value to
     *     setOptions().
     */
    public function __construct(mixed $options = null)
    {
        if (is_iterable($options)) {
            $this->setOptions($options);
        }
    }

    /**
     * @return $this
     */
    public function setNullOnAllEmpty(bool $nullOnAllEmpty): self
    {
        $this->nullOnAllEmpty = $nullOnAllEmpty;
        return $this;
    }

    public function isNullOnAllEmpty(): bool
    {
        return $this->nullOnAllEmpty;
    }

    /**
     * @return $this
     */
    public function setNullOnEmpty(bool $nullOnEmpty): self
    {
        $this->nullOnEmpty = $nullOnEmpty;
        return $this;
    }

    public function isNullOnEmpty(): bool
    {
        return $this->nullOnEmpty;
    }

    /**
     * Attempts to filter an array of date/time information to a formatted
     * string.
     *
     * @throws Exception\RuntimeException If filtering $value is impossible.
     * @psalm-return ($value is InputArray ? string : mixed|null)
     */
    public function filter(mixed $value): mixed
    {
        if (! is_array($value)) {
            // nothing to do
            return $value;
        }

        // Convert the date to a specific format
        if (
            $this->isNullOnEmpty()
            && array_reduce($value, self::reduce(...), false)
        ) {
            return null;
        }

        if (
            $this->isNullOnAllEmpty()
            && array_reduce($value, self::reduce(...), true)
        ) {
            return null;
        }

        ksort($value);
        $this->filterable($value);

        /** @psalm-var array<array-key, string> $value Forcing the type here because it has already been asserted */

        return vsprintf($this->format, $value);
    }

    /**
     * Ensures there are enough inputs in the array to properly format the date.
     *
     * @throws Exception\RuntimeException
     * @psalm-assert TInput $value
     */
    protected function filterable(array $value): void
    {
        if (count($value) !== $this->expectedInputs) {
            throw new Exception\RuntimeException(
                sprintf(
                    'There are not enough values in the array to filter this date (Required: %d, Received: %d)',
                    $this->expectedInputs,
                    count($value)
                )
            );
        }
    }

    /**
     * Reduce to a single value
     */
    private static function reduce(bool $soFar, string|null $value): bool
    {
        return $soFar || ($value === null || $value === '');
    }
}
