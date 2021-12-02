<?php

declare(strict_types=1);

namespace LaminasTest\Filter\TestAsset;

use Laminas\Filter\AbstractFilter;

use function strtolower;

class LowerCase extends AbstractFilter
{
    public function filter($value)
    {
        return strtolower($value);
    }
}
