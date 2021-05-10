<?php

namespace Laminas\Filter\Word;

class CamelCaseToUnderscore extends CamelCaseToSeparator
{
    public function __construct()
    {
        parent::__construct('_');
    }
}
