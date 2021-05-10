<?php

namespace Laminas\Filter\Word;

class DashToUnderscore extends SeparatorToSeparator
{
    public function __construct()
    {
        parent::__construct('-', '_');
    }
}
