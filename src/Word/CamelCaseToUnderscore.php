<?php

declare(strict_types=1);

namespace Laminas\Filter\Word;

class CamelCaseToUnderscore extends CamelCaseToSeparator
{
    public function __construct()
    {
        parent::__construct('_');
    }
}
