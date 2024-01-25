<?php

declare(strict_types=1);

namespace LaminasTest\Filter\StaticAnalysis;

use Laminas\Filter\AllowList;

/** @psalm-suppress UnusedClass */
final class AllowListChecks
{
    public function filterReturnTypeIsUnionOfInputAndNull(int $value): int|null
    {
        $filter = new AllowList(['list' => [1, 2, 3]]);

        return $filter->filter($value);
    }

    public function invokeReturnTypeIsUnionOfInputAndNull(int $value): int|null
    {
        $filter = new AllowList(['list' => [1, 2, 3]]);

        return $filter($value);
    }

    /** @return 99|null */
    public function testTypesAreRestrictedToExpectedValues(): int|null
    {
        $filter = new AllowList(['list' => [1, 2, 3]]);

        return $filter->filter(99);
    }
}
