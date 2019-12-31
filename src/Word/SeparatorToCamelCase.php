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
class SeparatorToCamelCase extends AbstractSeparator
{
    /**
     * Defined by Laminas\Filter\Filter
     *
     * @param  string $value
     * @return string
     */
    public function filter($value)
    {
        // a unicode safe way of converting characters to \x00\x00 notation
        $pregQuotedSeparator = preg_quote($this->separator, '#');

        if (self::hasPcreUnicodeSupport()) {
            parent::setPattern(array('#(' . $pregQuotedSeparator.')(\p{L}{1})#eu','#(^\p{Ll}{1})#eu'));
            if (!extension_loaded('mbstring')) {
                parent::setReplacement(array("strtoupper('\\2')","strtoupper('\\1')"));
            } else {
                parent::setReplacement(array("mb_strtoupper('\\2', 'UTF-8')","mb_strtoupper('\\1', 'UTF-8')"));
            }
        } else {
            parent::setPattern(array('#(' . $pregQuotedSeparator.')([A-Za-z]{1})#e','#(^[A-Za-z]{1})#e'));
            parent::setReplacement(array("strtoupper('\\2')","strtoupper('\\1')"));
        }

        return parent::filter($value);
    }
}
