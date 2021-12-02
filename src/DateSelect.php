<?php

declare(strict_types=1);

namespace Laminas\Filter;

class DateSelect extends AbstractDateDropdown
{
    /**
     * Year-Month-Day
     *
     * @var string
     */
    protected $format = '%3$s-%2$s-%1$s';

    /** @var int */
    protected $expectedInputs = 3;
}
