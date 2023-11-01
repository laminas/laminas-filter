<?php

declare(strict_types=1);

namespace Laminas\Filter\Word;

use Closure;

use function str_replace;

/**
 * @psalm-type Options = array{
 *     separator?: string,
 *     ...
 * }
 * @template TOptions of Options
 * @extends AbstractSeparator<TOptions>
 * @final
 */
class DashToSeparator extends AbstractSeparator
{
    public function filter(mixed $value): mixed
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
        return str_replace('-', $this->separator, $value);
    }
}
