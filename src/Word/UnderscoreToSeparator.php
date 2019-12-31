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
class UnderscoreToSeparator extends SeparatorToSeparator
{
    /**
     * Constructor
     *
     * @param  string $separator Space by default
     */
    public function __construct($replacementSeparator = ' ')
    {
        parent::__construct('_', $replacementSeparator);
    }
}
