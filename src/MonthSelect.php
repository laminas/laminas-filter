<?php

declare(strict_types=1);

namespace Laminas\Filter;

/**
 * @psalm-type Options = array{
 *     null_on_empty?: bool,
 *     null_on_all_empty?: bool,
 *     ...
 * }
 * @template TOptions of Options
 * @template-extends AbstractDateDropdown<TOptions>
 */
class MonthSelect extends AbstractDateDropdown
{
    /**
     * Year-Month
     *
     * @var string
     */
    protected $format = '%2$s-%1$s';

    /** @var int */
    protected $expectedInputs = 2;
}
