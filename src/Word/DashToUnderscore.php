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
 */
class DashToUnderscore extends SeparatorToSeparator
{
    public function __construct()
    {
        parent::__construct('-', '_');
    }
}
