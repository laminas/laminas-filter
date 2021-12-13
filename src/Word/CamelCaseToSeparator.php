<?php

declare(strict_types=1);

namespace Laminas\Filter\Word;

use Laminas\Stdlib\StringUtils;

use function is_array;
use function is_scalar;
use function is_string;
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
        if (! is_array($value)) {
            if (! is_scalar($value)) {
                return $value;
            }
            if (! is_string($value)) {
                $value = (string) $value;
            }
        }

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
