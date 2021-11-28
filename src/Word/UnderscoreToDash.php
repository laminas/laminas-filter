<?php

declare(strict_types=1);

namespace Laminas\Filter\Word;

class UnderscoreToDash extends SeparatorToSeparator
{
    public function __construct()
    {
        parent::__construct('_', '-');
    }
}
