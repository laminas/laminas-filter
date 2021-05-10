<?php

namespace LaminasTest\Filter\TestAsset;

use Laminas\Filter\AbstractFilter;

class StripUpperCase extends AbstractFilter
{
    public function filter($value)
    {
        return preg_replace('/[A-Z]/', '', $value);
    }
}
