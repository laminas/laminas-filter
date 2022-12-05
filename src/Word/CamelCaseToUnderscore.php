<?php

declare(strict_types=1);

namespace Laminas\Filter\Word;

/**
 * @psalm-type Options = array{
 *     separator?: string,
 *     ...
 * }
 * @template TOptions of Options
 * @extends CamelCaseToSeparator<TOptions>
 */
class CamelCaseToUnderscore extends CamelCaseToSeparator
{
    public function __construct()
    {
        parent::__construct('_');
    }
}
