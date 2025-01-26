<?php

declare(strict_types=1);

namespace Laminas\Filter;

use Laminas\Stdlib\PriorityQueue;
use Psr\Container\ContainerExceptionInterface;

/**
 * @psalm-type InstanceType = FilterInterface|callable(mixed): mixed
 * @psalm-type ChainSpec = array{
 *     filters?: list<array{
 *         name: string|class-string<FilterInterface>,
 *         options?: array<string, mixed>,
 *         priority?: int|null,
 *     }>,
 *     callbacks?: list<array{
 *         callback: FilterInterface|callable(mixed): mixed,
 *         priority?: int|null,
 *     }>,
 * }
 * @implements FilterChainInterface<mixed>
 */
final readonly class ImmutableFilterChain implements FilterChainInterface
{
    /** @var PriorityQueue<InstanceType, int> */
    private PriorityQueue $filters;

    /** @param PriorityQueue<InstanceType, int>|null $filters */
    private function __construct(
        private FilterPluginManager $pluginManager,
        PriorityQueue|null $filters,
    ) {
        /** @var PriorityQueue<InstanceType, int> $default */
        $default       = new PriorityQueue();
        $this->filters = $filters ?? $default;
    }

    public function filter(mixed $value): mixed
    {
        foreach ($this->filters as $filter) {
            /** @var mixed $value */
            $value = $filter($value);
        }

        return $value;
    }

    public function __invoke(mixed $value): mixed
    {
        return $this->filter($value);
    }

    public static function empty(FilterPluginManager $pluginManager): self
    {
        return new self($pluginManager, null);
    }

    /**
     * Construct a filter chain from a specification
     *
     * @param ChainSpec $spec
     * @throws ContainerExceptionInterface If any named filters cannot be retrieved from the plugin manager.
     */
    public static function fromArray(array $spec, FilterPluginManager $pluginManager): self
    {
        /** @psalm-var PriorityQueue<InstanceType, int> $queue */
        $queue = new PriorityQueue();
        $chain = new self($pluginManager, $queue);

        $callables = $spec['callbacks'] ?? [];
        foreach ($callables as $set) {
            $chain = $chain->attach($set['callback'], $set['priority'] ?? self::DEFAULT_PRIORITY);
        }

        $filters = $spec['filters'] ?? [];
        foreach ($filters as $filter) {
            $chain = $chain->attachByName(
                $filter['name'],
                $filter['options'] ?? [],
                $filter['priority'] ?? self::DEFAULT_PRIORITY,
            );
        }

        return $chain;
    }

    public function attach(FilterInterface|callable $callback, int $priority = self::DEFAULT_PRIORITY): self
    {
        $filters = clone $this->filters;
        $filters->insert($callback, $priority);

        return new self($this->pluginManager, $filters);
    }

    public function attachByName(string $name, array $options = [], int $priority = self::DEFAULT_PRIORITY): self
    {
        /** @psalm-var FilterInterface $filter */
        $filter  = $this->pluginManager->build($name, $options);
        $filters = clone $this->filters;
        $filters->insert($filter, $priority);

        return new self($this->pluginManager, $filters);
    }
}
