<?php

namespace LaminasTest\Filter\TestAsset;

use Laminas\Filter\AbstractFilter;

class LowerCase extends AbstractFilter
{
    public function filter($value)
    {
        return strtolower($value);
    }
}
