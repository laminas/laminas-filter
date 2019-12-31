<?php

/**
 * @see       https://github.com/laminas/laminas-filter for the canonical source repository
 * @copyright https://github.com/laminas/laminas-filter/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-filter/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\Filter\Word;

/**
 * @category   Laminas
 * @package    Laminas_Filter
 */
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
        if (self::hasPcreUnicodeSupport()) {
            parent::setPattern(array('#(?<=(?:\p{Lu}))(\p{Lu}\p{Ll})#','#(?<=(?:\p{Ll}|\p{Nd}))(\p{Lu})#'));
            parent::setReplacement(array($this->separator . '\1', $this->separator . '\1'));
        } else {
            parent::setPattern(array('#(?<=(?:[A-Z]))([A-Z]+)([A-Z][A-z])#', '#(?<=(?:[a-z0-9]))([A-Z])#'));
            parent::setReplacement(array('\1' . $this->separator . '\2', $this->separator . '\1'));
        }

        return parent::filter($value);
    }
}
