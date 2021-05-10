<?php

namespace Laminas\Filter\Word;

class UnderscoreToDash extends SeparatorToSeparator
{
    public function __construct()
    {
        parent::__construct('_', '-');
    }
}
