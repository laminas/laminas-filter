<?php

declare(strict_types=1);

namespace Laminas\Filter\Word;

/**
 * @psalm-type Options = array{
 *     search_separator?: string,
 *     replacement_separator?: string,
 *     ...
 * }
 * @template TOptions of Options
 * @template-extends SeparatorToSeparator<TOptions>
 * @final
 */
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
