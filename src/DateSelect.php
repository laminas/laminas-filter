<?php

declare(strict_types=1);

namespace Laminas\Filter;

use DateTime;

use function is_array;
use function is_numeric;
use function sprintf;

/**
 * @psalm-type Options = array{
 *     null_on_empty?: bool,
 *     null_on_all_empty?: bool,
 * }
 * @implements FilterInterface<string|null>
 */
final class DateSelect implements FilterInterface
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

        $day   = $this->getValue($value, 'day');
        $month = $this->getValue($value, 'month');
        $year  = $this->getValue($value, 'year');

        if ($this->returnNullIfAnyFieldEmpty && ($day === null || $month === null || $year === null)) {
            return null;
        }

        if ($this->returnNullIfAllFieldsEmpty && $day === null && $month === null && $year === null) {
            return null;
        }

        if ($day === null || $month === null || $year === null) {
            return $value;
        }

        if (! $this->isParsableAsDateValue($day, $month, $year)) {
            /** @psalm-var T */
            return $value;
        }

        return sprintf('%d-%02d-%02d', $year, $month, $day);
    }

    /**
     * @psalm-assert-if-true int $day
     * @psalm-assert-if-true int $month
     * @psalm-assert-if-true int $year
     */
    private function isParsableAsDateValue(mixed $day, mixed $month, mixed $year): bool
    {
        if (! is_numeric($day) || ! is_numeric($month) || ! is_numeric($year)) {
            return false;
        }

        $date = DateTime::createFromFormat('Y-m-d', $year . '-' . $month . '-' . $day);

        if (! $date || $date->format('Ymd') !== sprintf('%d%02d%02d', $year, $month, $day)) {
            return false;
        }

        return true;
    }

    /** @param mixed[] $value */
    private function getValue(array $value, string $string): mixed
    {
        /** @var mixed $result */
        $result = $value[$string] ?? null;
        return $result === '' ? null : $result;
    }
}
