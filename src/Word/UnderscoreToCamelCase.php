<?php

namespace Laminas\Filter\Word;

class UnderscoreToCamelCase extends SeparatorToCamelCase
{
    public function __construct()
    {
        parent::__construct('_');
    }
}
