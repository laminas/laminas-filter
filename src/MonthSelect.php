<?php

namespace Laminas\Filter;

class MonthSelect extends AbstractDateDropdown
{
    /**
     * Year-Month
     *
     * @var string
     */
    protected $format = '%2$s-%1$s';

    /**
     * @var int
     */
    protected $expectedInputs = 2;
}
