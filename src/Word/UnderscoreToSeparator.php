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
class UnderscoreToSeparator extends SeparatorToSeparator
{
    /**
     * @param string $replacementSeparator Space by default
     */
    public function __construct($replacementSeparator = ' ')
    {
        parent::__construct('_', $replacementSeparator);
    }
}
