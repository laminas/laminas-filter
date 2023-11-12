<?php

declare(strict_types=1);

namespace LaminasTest\Filter\StaticAnalysis;

use Laminas\Filter\AllowList;

/** @psalm-suppress UnusedClass */
final class AllowListChecks
{
    public function filterReturnTypeIsUnionOfInputAndNull(int $value): int|null
    {
        $filter = new AllowList();

        return $filter->filter($value);
    }

    public function invokeReturnTypeIsUnionOfInputAndNull(int $value): int|null
    {
        $filter = new AllowList();

        return $filter($value);
    }
}
