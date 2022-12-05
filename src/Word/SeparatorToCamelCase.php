<?php

declare(strict_types=1);

namespace Laminas\Filter\Word;

use Closure;
use Laminas\Stdlib\StringUtils;

use function mb_strtoupper;
use function preg_quote;
use function preg_replace_callback;
use function strtoupper;

/**
 * @psalm-type Options = array{
 *     separator?: string,
 *     ...
 * }
 * @template TOptions of Options
 * @extends AbstractSeparator<TOptions>
 */
class SeparatorToCamelCase extends AbstractSeparator
{
    /**
     * @param mixed $value
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
        // a unicode safe way of converting characters to \x00\x00 notation
        $pregQuotedSeparator = preg_quote($this->separator, '#');

        if (StringUtils::hasPcreUnicodeSupport()) {
            $patterns     = [
                '#(' . $pregQuotedSeparator . ')(\P{Z}{1})#u',
                '#(^\P{Z}{1})#u',
            ];
            $replacements = [
                static fn($matches): string => mb_strtoupper($matches[2], 'UTF-8'),
                static fn($matches): string => mb_strtoupper($matches[1], 'UTF-8'),
            ];
        } else {
            $patterns     = [
                '#(' . $pregQuotedSeparator . ')([\S]{1})#',
                '#(^[\S]{1})#',
            ];
            $replacements = [
                static fn($matches): string => strtoupper($matches[2]),
                static fn($matches): string => strtoupper($matches[1]),
            ];
        }

        $filtered = $value;
        foreach ($patterns as $index => $pattern) {
            $filtered = preg_replace_callback($pattern, $replacements[$index], $filtered);
        }
        return $filtered;
    }
}
