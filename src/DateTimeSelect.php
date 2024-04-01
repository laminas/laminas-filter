<?php

declare(strict_types=1);

namespace Laminas\Filter;

use function is_array;
use function ksort;
use function vsprintf;

/**
 * @psalm-type Options = array{
 *     null_on_empty?: bool,
 *     null_on_all_empty?: bool,
 *     ...
 * }
 * @psalm-type InputArray = array{
 *     year: numeric,
 *     month: numeric,
 *     day: numeric,
 *     hour: numeric,
 *     minute: numeric,
 *     second: numeric,
 * }
 * @template TOptions of Options
 * @template-extends AbstractDateDropdown<TOptions, InputArray>
 */
final class DateTimeSelect extends AbstractDateDropdown
{
    /**
     * Year-Month-Day Hour:Min:Sec
     */
    protected string $format      = '%6$s-%4$s-%1$s %2$s:%3$s:%5$s';
    protected int $expectedInputs = 6;

    /** @inheritDoc */
    public function filter(mixed $value): mixed
    {
        if (! is_array($value)) {
            // nothing to do
            return $value;
        }

        if (
            $this->isNullOnEmpty()
            && (
                empty($value['year'])
                || empty($value['month'])
                || empty($value['day'])
                || empty($value['hour'])
                || empty($value['minute'])
                || (isset($value['second']) && empty($value['second']))
            )
        ) {
            return null;
        }

        if (
            $this->isNullOnAllEmpty()
            && (
                empty($value['year'])
                && empty($value['month'])
                && empty($value['day'])
                && empty($value['hour'])
                && empty($value['minute'])
                && empty($value['second'])
            )
        ) {
            // Cannot handle this value
            return null;
        }

        if (! isset($value['second'])) {
            $value['second'] = '00';
        }

        $this->filterable($value);

        ksort($value);

        return vsprintf($this->format, $value);
    }
}
