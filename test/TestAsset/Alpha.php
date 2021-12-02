<?php

declare(strict_types=1);

namespace LaminasTest\Filter\TestAsset;

use Laminas\Filter\AbstractFilter;

use function is_array;
use function is_scalar;
use function preg_replace;

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
