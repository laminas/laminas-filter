<?php

declare(strict_types=1);

namespace LaminasTest\Filter\TestAsset;

use Laminas\Filter\FilterInterface;

use function str_repeat;

class StrRepeatFilterInterface implements FilterInterface
{
    public function filter($value)
    {
        return str_repeat((string) $value, 2);
    }
}
