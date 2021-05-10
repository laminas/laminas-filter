<?php

namespace Laminas\Filter\Word;

class UnderscoreToSeparator extends SeparatorToSeparator
{
    /**
     * Constructor
     *
     * @param  string $replacementSeparator Space by default
     */
    public function __construct($replacementSeparator = ' ')
    {
        parent::__construct('_', $replacementSeparator);
    }
}
