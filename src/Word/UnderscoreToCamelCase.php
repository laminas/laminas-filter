<?php

declare(strict_types=1);

namespace Laminas\Filter\Word;

/**
 * @psalm-type Options = array{
 *     separator?: string,
 *     ...
 * }
 * @template TOptions of Options
 * @extends SeparatorToCamelCase<TOptions>
 */
class UnderscoreToCamelCase extends SeparatorToCamelCase
{
    public function __construct()
    {
        parent::__construct('_');
    }
}
