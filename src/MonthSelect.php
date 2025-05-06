<?php

declare(strict_types=1);

namespace Laminas\Filter;

use function filter_var;
use function is_array;
use function is_numeric;
use function sprintf;

use const FILTER_VALIDATE_INT;

/**
 * @psalm-type Options = array{
 *     null_on_empty?: bool,
 *     null_on_all_empty?: bool,
 * }
 * @implements FilterInterface<string|null>
 */
final class MonthSelect implements FilterInterface
{
    private readonly bool $returnNullIfAnyFieldEmpty;
    private readonly bool $returnNullIfAllFieldsEmpty;

    /** @param Options $options */
    public function __construct(array $options = [])
    {
        $this->returnNullIfAnyFieldEmpty  = $options['null_on_empty'] ?? false;
        $this->returnNullIfAllFieldsEmpty = $options['null_on_all_empty'] ?? false;
    }

    public function __invoke(mixed $value): mixed
    {
        return $this->filter($value);
    }

    /**
     * Returns the result of filtering $value
     *
     * @template T
     * @param T $value
     * @return string|null|T
     */
    public function filter(mixed $value): mixed
    {
        if (! is_array($value)) {
            return $value;
        }

        $month = $value['month'] ?? null;
        /** @var mixed $month */
        $month = $month === '' ? null : $month;

        $year = $value['year'] ?? null;
        /** @var mixed $year */
        $year = $year === '' ? null : $year;

        if ($this->returnNullIfAnyFieldEmpty && ($month === null || $year === null)) {
            return null;
        }

        if ($this->returnNullIfAllFieldsEmpty && $month === null && $year === null) {
            return null;
        }

        if (! $this->isParsableAsDateValue($month, 1, 12) || ! $this->isParsableAsDateValue($year, 0, 9999)) {
            /** @psalm-var T */
            return $value;
        }

        return sprintf('%d-%02d', $year, $month);
    }

    /** @psalm-assert-if-true int $value */
    private function isParsableAsDateValue(mixed $value, int $lowestValue, int $highestValue): bool
    {
        if (
            ! is_numeric($value)
            || filter_var(
                $value,
                FILTER_VALIDATE_INT,
                ['options' => ['min_range' => $lowestValue, 'max_range' => $highestValue]]
            ) === false
        ) {
            return false;
        }

        return true;
    }
}
