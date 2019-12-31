<?php

/**
 * @see       https://github.com/laminas/laminas-filter for the canonical source repository
 * @copyright https://github.com/laminas/laminas-filter/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-filter/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\Filter\Word;

use Laminas\Stdlib\StringUtils;

class CamelCaseToSeparator extends AbstractSeparator
{
    /**
     * Defined by Laminas\Filter\Filter
     *
     * @param  string $value
     * @return string
     */
    public function filter($value)
    {
        if (StringUtils::hasPcreUnicodeSupport()) {
            $pattern     = array('#(?<=(?:\p{Lu}))(\p{Lu}\p{Ll})#', '#(?<=(?:\p{Ll}|\p{Nd}))(\p{Lu})#');
            $replacement = array($this->separator . '\1', $this->separator . '\1');
        } else {
            $pattern     = array('#(?<=(?:[A-Z]))([A-Z]+)([A-Z][a-z])#', '#(?<=(?:[a-z0-9]))([A-Z])#');
            $replacement = array('\1' . $this->separator . '\2', $this->separator . '\1');
        }

        return preg_replace($pattern, $replacement, $value);
    }
}
