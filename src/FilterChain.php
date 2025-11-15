<?php

declare(strict_types=1);

namespace Laminas\Filter;

use Countable;
use IteratorAggregate;
use Laminas\Filter\Exception\InvalidSpecificationArrayException;
use Laminas\Stdlib\PriorityQueue;
use Psr\Container\ContainerExceptionInterface;
use Traversable;

use function count;
use function is_array;
use function is_callable;
use function is_int;
use function is_string;

/**
 * @psalm-type InstanceType = FilterInterface|(callable(mixed): mixed)
 * @psalm-type FilterSpecification = array{
 *     name: string|class-string<FilterInterface>,
 *     options?: array<string, mixed>,
 *     priority?: int,
 * }|InstanceType
 * @psalm-type FilterChainConfiguration = array{
 *     filters?: list<FilterSpecification>,
 *     callbacks?: list<array{
 *         callback: FilterInterface|(callable(mixed): mixed),
 *         priority?: int,
 *     }>
 * }
 * @implements IteratorAggregate<array-key, InstanceType>
 * @implements FilterChainInterface<mixed>
 */
final class FilterChain implements FilterChainInterface, Countable, IteratorAggregate
{
    /** @var PriorityQueue<InstanceType, int> */
    private PriorityQueue $filters;

    /**
     * @param FilterChainConfiguration $options
     * @throws ContainerExceptionInterface If any filter cannot be retrieved from the plugin manager.
     */
    public function __construct(
        private readonly FilterPluginManager $plugins,
        array $options = [],
    ) {
        /** @var PriorityQueue<InstanceType, int> $priorityQueue */
        $priorityQueue = new PriorityQueue();
        $this->filters = $priorityQueue;

        $callbacks = $options['callbacks'] ?? [];
        foreach ($callbacks as $spec) {
            $this->attach(
                $spec['callback'],
                $spec['priority'] ?? self::DEFAULT_PRIORITY,
            );
        }

        $filters = $options['filters'] ?? [];
        foreach ($filters as $spec) {
            if (is_callable($spec) || $spec instanceof FilterInterface) {
                $this->attach($spec);
                continue;
            }

            $this->attachByName(
                $spec['name'],
                $spec['options'] ?? [],
                $spec['priority'] ?? self::DEFAULT_PRIORITY,
            );
        }
    }

    /** Return the count of attached filters */
    public function count(): int
    {
        return count($this->filters);
    }

    public function attach(FilterInterface|callable $callback, int $priority = self::DEFAULT_PRIORITY): self
    {
        $this->filters->insert($callback, $priority);

        return $this;
    }

    public function attachByName(string $name, array $options = [], int $priority = self::DEFAULT_PRIORITY): self
    {
        if ($options === []) {
            /** @psalm-var FilterInterface $filter */
            $filter = $this->plugins->get($name);
        } else {
            /** @psalm-var FilterInterface $filter */
            $filter = $this->plugins->build($name, $options);
        }

        return $this->attach($filter, $priority);
    }

    /**
     * Merge the filter chain with the one given in parameter
     *
     * @return $this
     */
    public function merge(FilterChain $filterChain): self
    {
        foreach ($filterChain->filters->toArray(PriorityQueue::EXTR_BOTH) as $item) {
            $this->attach($item['data'], $item['priority']);
        }

        return $this;
    }

    public function filter(mixed $value): mixed
    {
        foreach ($this as $filter) {
            /** @var mixed $value */
            $value = $filter($value);
        }

        return $value;
    }

    public function __invoke(mixed $value): mixed
    {
        return $this->filter($value);
    }

    /**
     * Prevent clones from mutating the composed priority queue
     */
    public function __clone()
    {
        $this->filters = clone $this->filters;
    }

    /** @return Traversable<array-key, FilterInterface|callable(mixed): mixed> */
    public function getIterator(): Traversable
    {
        return clone $this->filters;
    }

    /**
     * @psalm-assert FilterChainConfiguration $spec
     * @throws InvalidSpecificationArrayException If the specification is invalid.
     */
    public static function validateSpecification(array $spec): void
    {
        /** @psalm-var mixed $filters */
        $filters = $spec['filters'] ?? null;
        /** @psalm-var mixed $callbacks */
        $callbacks = $spec['callbacks'] ?? null;

        if ($filters === null && $callbacks === null) {
            return; // An effectively empty specification is OK
        }

        if ($filters !== null) {
            self::validateFilterList($filters);
        }

        if ($callbacks !== null) {
            self::validateCallbackList($callbacks);
        }
    }

    private static function validateFilterList(mixed $spec): void
    {
        if (! is_array($spec)) {
            throw InvalidSpecificationArrayException::becauseTheFilterListMustBeAnArray($spec);
        }

        /** @psalm-var mixed $item */
        foreach ($spec as $item) {
            self::validateFilterSpecification($item);
        }
    }

    private static function validateFilterSpecification(mixed $spec): void
    {
        if (is_callable($spec) || $spec instanceof FilterInterface) {
            return;
        }

        if (! is_array($spec)) {
            throw InvalidSpecificationArrayException::becauseFilterSpecMustBeAnArray($spec);
        }

        $name     = $spec['name'] ?? null;
        $options  = $spec['options'] ?? null;
        $priority = $spec['priority'] ?? null;

        if (! is_string($name) || $name === '') {
            throw InvalidSpecificationArrayException::becauseFilterNamesMustBeAString($name ?? null);
        }

        if ($options !== null && ! is_array($options)) {
            throw InvalidSpecificationArrayException::becauseOptionsShouldBeArrays($options);
        }

        if ($priority !== null && ! is_int($priority)) {
            throw InvalidSpecificationArrayException::becauseFilterPriorityMustBeAnInteger($priority);
        }
    }

    private static function validateCallbackList(mixed $spec): void
    {
        if (! is_array($spec)) {
            throw InvalidSpecificationArrayException::becauseCallbackListMustBeAList($spec);
        }

        /** @psalm-var mixed $item */
        foreach ($spec as $item) {
            self::validateCallback($item);
        }
    }

    private static function validateCallback(mixed $spec): void
    {
        if (! is_array($spec)) {
            throw InvalidSpecificationArrayException::becauseCallbackMustBeArray($spec);
        }

        $callback = $spec['callback'] ?? null;
        $priority = $spec['priority'] ?? null;

        if (! is_callable($callback)) {
            throw InvalidSpecificationArrayException::becauseCallbackMustBePresent($callback);
        }

        if ($priority !== null && ! is_int($priority)) {
            throw InvalidSpecificationArrayException::becauseFilterPriorityMustBeAnInteger($priority);
        }
    }
}
