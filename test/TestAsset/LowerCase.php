<?php

declare(strict_types=1);

namespace LaminasTest\Filter\TestAsset;

use Laminas\Filter\AbstractFilter;

use function strtolower;

/** @template-extends AbstractFilter<array{}> */
class LowerCase extends AbstractFilter
{
    public function filter(mixed $value): mixed
    {
        return strtolower($value);
    }
}
