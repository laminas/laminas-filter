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
final class AllowList implements FilterInterface
{
    private readonly bool $strict;
    /** @var list<mixed> */
    private readonly array $list;

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
     * Will return $value if its present in the allow-list. If $value is rejected then it will return null.
     */
    public function filter(mixed $value): mixed
    {
        return in_array($value, $this->list, $this->strict) ? $value : null;
    }

    /**
     * {@inheritDoc}
     *
     * Will return $value if its present in the allow-list. If $value is rejected then it will return null.
     */
    public function __invoke(mixed $value): mixed
    {
        return $this->filter($value);
    }
}
