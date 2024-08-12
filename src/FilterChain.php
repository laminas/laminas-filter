<?php

declare(strict_types=1);

namespace Laminas\Filter;

use Countable;
use IteratorAggregate;
use Laminas\ServiceManager\ServiceManager;
use Laminas\Stdlib\PriorityQueue;
use Traversable;

use function call_user_func;
use function count;
use function get_debug_type;
use function is_array;
use function is_callable;
use function is_string;
use function sprintf;
use function strtolower;

/**
 * @psalm-type InstanceType = FilterInterface|callable(mixed): mixed
 * @psalm-type FilterChainConfiguration = array{
 *    filters?: list<array{
 *        name: string|class-string<FilterInterface>,
 *        options?: array<string, mixed>,
 *        priority?: int,
 *    }>,
 *    callbacks?: list<array{
 *        callback: FilterInterface|callable(mixed): mixed,
 *        priority?: int,
 *    }>
 * }
 * @extends AbstractFilter<FilterChainConfiguration>
 * @implements IteratorAggregate<array-key, InstanceType>
 */
final class FilterChain extends AbstractFilter implements Countable, IteratorAggregate
{
    /**
     * Default priority at which filters are added
     */
    public const DEFAULT_PRIORITY = 1000;

    /** @var FilterPluginManager|null */
    protected $plugins;

    /**
     * Filter chain
     *
     * @var PriorityQueue<InstanceType, int>
     */
    protected $filters;

    /**
     * Initialize filter chain
     *
     * @param FilterChainConfiguration|Traversable|null $options
     */
    public function __construct($options = null)
    {
        $this->filters = new PriorityQueue();

        if (null !== $options) {
            $this->setOptions($options);
        }
    }

    /**
     * @param  FilterChainConfiguration|Traversable $options
     * @return $this
     * @throws Exception\InvalidArgumentException
     */
    public function setOptions($options)
    {
        if (! is_array($options) && ! $options instanceof Traversable) {
            throw new Exception\InvalidArgumentException(sprintf(
                'Expected array or Traversable; received "%s"',
                get_debug_type($options)
            ));
        }

        foreach ($options as $key => $value) {
            switch (strtolower($key)) {
                case 'callbacks':
                    foreach ($value as $spec) {
                        $callback = $spec['callback'] ?? false;
                        $priority = $spec['priority'] ?? static::DEFAULT_PRIORITY;
                        if (is_callable($callback) || $callback instanceof FilterInterface) {
                            $this->attach($callback, $priority);
                        }
                    }
                    break;
                case 'filters':
                    foreach ($value as $spec) {
                        $name     = $spec['name'] ?? false;
                        $options  = $spec['options'] ?? [];
                        $priority = $spec['priority'] ?? static::DEFAULT_PRIORITY;
                        if (is_string($name) && $name !== '') {
                            $this->attachByName($name, $options, $priority);
                        }
                    }
                    break;
                default:
                    // ignore other options
                    break;
            }
        }

        return $this;
    }

    /** Return the count of attached filters */
    public function count(): int
    {
        return count($this->filters);
    }

    /** Get plugin manager instance */
    public function getPluginManager(): FilterPluginManager
    {
        $plugins = $this->plugins;
        if (! $plugins instanceof FilterPluginManager) {
            $plugins = new FilterPluginManager(new ServiceManager());
            $this->setPluginManager($plugins);
        }

        return $plugins;
    }

    /**
     * Set plugin manager instance
     *
     * @return self
     */
    public function setPluginManager(FilterPluginManager $plugins)
    {
        $this->plugins = $plugins;
        return $this;
    }

    /**
     * Retrieve a filter plugin by name
     *
     * @template T of FilterInterface
     * @param class-string<T>|string $name
     * @return ($name is class-string<T> ? T : InstanceType)
     */
    public function plugin(string $name, array $options = [])
    {
        return $this->getPluginManager()->build($name, $options);
    }

    /**
     * Attach a filter to the chain
     *
     * @param  InstanceType $callback A Filter implementation or valid PHP callback
     * @param  int $priority Priority at which to enqueue filter; defaults to 1000 (higher executes earlier)
     * @throws Exception\InvalidArgumentException
     * @return self
     */
    public function attach(FilterInterface|callable $callback, int $priority = self::DEFAULT_PRIORITY)
    {
        if (! is_callable($callback)) {
            if (! $callback instanceof FilterInterface) {
                throw new Exception\InvalidArgumentException(sprintf(
                    'Expected a valid PHP callback; received "%s"',
                    get_debug_type($callback)
                ));
            }
            $callback = [$callback, 'filter'];
        }
        $this->filters->insert($callback, $priority);
        return $this;
    }

    /**
     * Attach a filter to the chain using a short name
     *
     * Retrieves the filter from the attached plugin manager, and then calls attach()
     * with the retrieved instance.
     *
     * @param class-string<FilterInterface>|string $name
     * @param  int $priority Priority at which to enqueue filter; defaults to 1000 (higher executes earlier)
     * @return self
     */
    public function attachByName(string $name, array $options = [], int $priority = self::DEFAULT_PRIORITY)
    {
        return $this->attach($this->plugin($name, $options), $priority);
    }

    /**
     * Merge the filter chain with the one given in parameter
     *
     * @return self
     */
    public function merge(FilterChain $filterChain)
    {
        foreach ($filterChain->filters->toArray(PriorityQueue::EXTR_BOTH) as $item) {
            $this->attach($item['data'], $item['priority']);
        }

        return $this;
    }

    /**
     * Get all the filters
     *
     * @return PriorityQueue<FilterInterface|callable(mixed): mixed, int>
     */
    public function getFilters()
    {
        return $this->filters;
    }

    /**
     * Returns $value filtered through each filter in the chain
     *
     * Filters are run in the order in which they were added to the chain (FIFO)
     *
     * @psalm-suppress MixedAssignment values are always mixed
     */
    public function filter(mixed $value): mixed
    {
        $valueFiltered = $value;
        foreach ($this as $filter) {
            if ($filter instanceof FilterInterface) {
                $valueFiltered = $filter->filter($valueFiltered);

                continue;
            }

            $valueFiltered = call_user_func($filter, $valueFiltered);
        }

        return $valueFiltered;
    }

    /**
     * Clone filters
     */
    public function __clone()
    {
        $this->filters = clone $this->filters;
    }

    /**
     * Prepare filter chain for serialization
     *
     * Plugin manager (property 'plugins') cannot
     * be serialized. On wakeup the property remains unset
     * and next invocation to getPluginManager() sets
     * the default plugin manager instance (FilterPluginManager).
     */
    public function __sleep()
    {
        return ['filters'];
    }

    /** @return Traversable<array-key, FilterInterface|callable(mixed): mixed> */
    public function getIterator(): Traversable
    {
        return clone $this->filters;
    }
}
