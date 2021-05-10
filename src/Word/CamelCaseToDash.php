<?php

namespace Laminas\Filter\Word;

class CamelCaseToDash extends CamelCaseToSeparator
{
    public function __construct()
    {
        parent::__construct('-');
    }
}
