<?php

declare(strict_types=1);

namespace Laminas\Filter;

use Countable;
use IteratorAggregate;
use Laminas\Stdlib\PriorityQueue;
use Psr\Container\ContainerExceptionInterface;
use Traversable;

use function count;

/**
 * @psalm-type InstanceType = FilterInterface|(callable(mixed): mixed)
 * @psalm-type FilterChainConfiguration = array{
 *    filters?: list<array{
 *        name: string|class-string<FilterInterface>,
 *        options?: array<string, mixed>,
 *        priority?: int,
 *    }>,
 *    callbacks?: list<array{
 *        callback: FilterInterface|(callable(mixed): mixed),
 *        priority?: int,
 *    }>
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
        /** @psalm-var FilterInterface $filter */
        $filter = $this->plugins->build($name, $options);

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
}
