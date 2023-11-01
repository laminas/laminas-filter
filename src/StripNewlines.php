<?php

declare(strict_types=1);

namespace Laminas\Filter;

use Closure;

use function str_replace;

/**
 * @psalm-type Options = array{}
 * @extends AbstractFilter<Options>
 * @final
 */
class StripNewlines extends AbstractFilter
{
    /**
     * Defined by Laminas\Filter\FilterInterface
     *
     * Returns $value without newline control characters
     *
     * @param  mixed $value
     * @return mixed
     */
    public function filter($value)
    {
        return self::applyFilterOnlyToStringableValuesAndStringableArrayValues(
            $value,
            Closure::fromCallable([$this, 'filterNormalizedValue'])
        );
    }

    /**
     * @param  string|string[] $value
     * @return string|string[]
     */
    private function filterNormalizedValue($value)
    {
        return str_replace(["\n", "\r"], '', $value);
    }
}
