<?php

declare(strict_types=1);

namespace Laminas\Filter;

use Laminas\Stdlib\ArrayUtils;

use function array_values;
use function in_array;
use function is_array;

/**
 * @psalm-type Options = array{
 *     strict?: bool,
 *     list?: iterable<array-key, mixed>,
 * }
 */
final class AllowList implements FilterInterface
{
    private bool $strict = false;
    /** @var list<mixed> */
    private array $list = [];

    /**
     * @param Options $options
     */
    public function __construct(array $options = [])
    {
        $this->setOptions($options);
    }

    /**
     * @param Options $options
     * @return $this
     */
    public function setOptions(array $options = []): self
    {
        $strict = $options['strict'] ?? false;
        $list   = $options['list'] ?? [];

        $this->setStrict($strict);
        $this->setList($list);

        return $this;
    }

    /**
     * Determine whether the in_array() call should be "strict" or not. See in_array docs.
     */
    public function setStrict(bool $strict): void
    {
        $this->strict = $strict;
    }

    /**
     * Returns whether the in_array() call should be "strict" or not. See in_array docs.
     */
    public function getStrict(): bool
    {
        return $this->strict;
    }

    /**
     * Set the list of items to allow
     *
     * @param iterable<array-key, mixed> $list
     */
    public function setList(iterable $list = []): void
    {
        if (! is_array($list)) {
            $list = ArrayUtils::iteratorToArray($list);
        }

        $this->list = array_values($list);
    }

    /**
     * Get the list of items to allow
     *
     * @return list<mixed>
     */
    public function getList(): array
    {
        return $this->list;
    }

    /**
     * {@inheritDoc}
     *
     * Will return $value if its present in the allow-list. If $value is rejected then it will return null.
     *
     * @template T
     * @param T $value
     * @return T|null
     */
    public function filter(mixed $value): mixed
    {
        return in_array($value, $this->list, $this->strict) ? $value : null;
    }

    /**
     * Will return $value if its present in the allow-list. If $value is rejected then it will return null.
     *
     * @template T
     * @param T $value
     * @return T|null
     */
    public function __invoke(mixed $value): mixed
    {
        return $this->filter($value);
    }
}
