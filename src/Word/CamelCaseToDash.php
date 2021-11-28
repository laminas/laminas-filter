<?php

declare(strict_types=1);

namespace Laminas\Filter\Word;

class CamelCaseToDash extends CamelCaseToSeparator
{
    public function __construct()
    {
        parent::__construct('-');
    }
}
