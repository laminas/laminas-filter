<?php

declare(strict_types=1);

namespace Laminas\Filter\Word;

class UnderscoreToCamelCase extends SeparatorToCamelCase
{
    public function __construct()
    {
        parent::__construct('_');
    }
}
