<?php

declare(strict_types=1);

namespace Laminas\Filter\Word;

use Closure;
use Laminas\Stdlib\StringUtils;

use function preg_replace;

class CamelCaseToSeparator extends AbstractSeparator
{
    /**
     * Defined by Laminas\Filter\Filter
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
