<?php

namespace LaminasTest\Filter\TestAsset;

use Laminas\Filter\AbstractFilter;

class Alpha extends AbstractFilter
{
    public function filter($value)
    {
        if (! is_scalar($value) && ! is_array($value)) {
            return $value;
        }

        return preg_replace('/[^a-zA-Z\s]/', '', $value);
    }
}
