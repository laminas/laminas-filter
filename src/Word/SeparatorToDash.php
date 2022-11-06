<?php

declare(strict_types=1);

namespace Laminas\Filter\Word;

class SeparatorToDash extends SeparatorToSeparator
{
    /**
     * @param string $searchSeparator Separator to search for change
     */
    public function __construct($searchSeparator = ' ')
    {
        parent::__construct($searchSeparator, '-');
    }
}
