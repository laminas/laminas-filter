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
class DashToSeparator extends AbstractSeparator
{
    /**
     * Defined by Laminas\Filter\Filter
     *
     * @param  string $value
     * @return string
     */
    public function filter($value)
    {
        $this->setPattern('#-#');
        $this->setReplacement($this->separator);
        return parent::filter($value);
    }
}
