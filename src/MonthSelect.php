<?php

declare(strict_types=1);

namespace Laminas\Filter;

/**
 * @psalm-type Options = array{
 *     null_on_empty?: bool,
 *     null_on_all_empty?: bool,
 *     ...
 * }
 * @psalm-type InputArray = array{
 *       year: numeric,
 *       month: numeric,
 *  }
 * @template TOptions of Options
 * @template-extends AbstractDateDropdown<TOptions, InputArray>
 */
final class MonthSelect extends AbstractDateDropdown
{
    /**
     * Year-Month
     */
    protected string $format      = '%2$s-%1$s';
    protected int $expectedInputs = 2;
}
