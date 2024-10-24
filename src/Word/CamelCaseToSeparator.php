<?php

declare(strict_types=1);

namespace Laminas\Filter\Word;

use Laminas\Filter\FilterInterface;
use Laminas\Filter\ScalarOrArrayFilterCallback;

use function implode;
use function preg_split;

use const PREG_SPLIT_DELIM_CAPTURE;
use const PREG_SPLIT_NO_EMPTY;

/**
 * @psalm-type Options = array{
 *     separator?: string,
 * }
 * @template TOptions of Options
 * @implements FilterInterface<string|array<array-key, string|mixed>>
 */
final class CamelCaseToSeparator implements FilterInterface
{
    private readonly string $separator;

    /** @param Options $options */
    public function __construct(array $options = [])
    {
        $this->separator = $options['separator'] ?? ' ';
    }

    public function __invoke(mixed $value): mixed
    {
        return $this->filter($value);
    }

    public function filter(mixed $value): mixed
    {
        return ScalarOrArrayFilterCallback::applyRecursively(
            $value,
            fn (string $input): string =>
            implode(
                $this->separator,
                preg_split(
                    '#(\p{Lu}\p{Ll}+)#',
                    $input,
                    -1,
                    PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY
                )
            )
        );
    }
}
