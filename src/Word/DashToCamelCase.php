<?php

namespace Laminas\Filter\Word;

class DashToCamelCase extends SeparatorToCamelCase
{
    public function __construct()
    {
        parent::__construct('-');
    }
}
