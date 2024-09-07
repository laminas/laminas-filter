<?php

declare(strict_types=1);

namespace Laminas\Filter;

use Laminas\Stdlib\ArrayUtils;

use function array_values;
use function in_array;

/**
 * @psalm-type Options = array{
 *     strict?: bool,
 *     list?: iterable<array-key, mixed>,
 * }
 * @implements FilterInterface<null>
 */
final class DenyList implements FilterInterface
{
    private readonly array $list;
    private readonly bool $strict;

    /** @param Options $options */
    public function __construct(array $options = [])
    {
        $this->strict = $options['strict'] ?? false;
        $list         = ArrayUtils::iteratorToArray($options['list'] ?? []);
        $this->list   = array_values($list);
    }

    /**
     * {@inheritDoc}
     *
     * Will return null if $value is present in the deny-list. If $value is NOT present then it will return $value.
     */
    public function filter(mixed $value): mixed
    {
        return in_array($value, $this->list, $this->strict) ? null : $value;
    }

    /**
     * {@inheritDoc}
     *
     * Will return null if $value is present in the deny-list. If $value is NOT present then it will return $value.
     */
    public function __invoke(mixed $value): mixed
    {
        return $this->filter($value);
    }
}
