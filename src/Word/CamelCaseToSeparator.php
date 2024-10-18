<?php

declare(strict_types=1);

namespace Laminas\Filter\Word;

use Closure;
use Laminas\Stdlib\StringUtils;

use function preg_replace;

/**
 * @psalm-type Options = array{
 *     separator?: string,
 *     ...
 * }
 * @template TOptions of Options
 * @extends AbstractSeparator<TOptions>
 */
class CamelCaseToSeparator extends AbstractSeparator
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
    private function filterNormalizedValue(string|array $value): string|array
    {
        if (StringUtils::hasPcreUnicodeSupport()) {
            $pattern     = ['#(?<=(?:\p{Lu}))(\p{Lu}\p{Ll})#', '#(?<=(?:\p{Ll}|\p{Nd}))(\p{Lu})#'];
            $replacement = [$this->separator . '\1', $this->separator . '\1'];
        } else {
            $pattern     = ['#(?<=(?:[A-Z]))([A-Z]+)([A-Z][a-z])#', '#(?<=(?:[a-z0-9]))([A-Z])#'];
            $replacement = ['\1' . $this->separator . '\2', $this->separator . '\1'];
        }

        return preg_replace($pattern, $replacement, $value);
    }
}
