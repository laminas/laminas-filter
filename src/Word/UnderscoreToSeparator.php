<?php

declare(strict_types=1);

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
