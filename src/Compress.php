<?php

declare(strict_types=1);

namespace Laminas\Filter;

use Laminas\Filter\Compress\AbstractCompressionAlgorithm;
use Laminas\Filter\Compress\CompressionAlgorithmInterface;
use Laminas\Stdlib\ArrayUtils;
use Traversable;

use function call_user_func_array;
use function class_exists;
use function get_debug_type;
use function is_a;
use function is_array;
use function is_string;
use function method_exists;
use function sprintf;
use function ucfirst;

/**
 * Compresses a given string
 *
 * @psalm-type AdapterType = 'Bz2'|'Gz'|'Tar'|'Zip'|class-string<CompressionAlgorithmInterface>
 * @psalm-type AdapterTypeOrInstance = CompressionAlgorithmInterface|AdapterType
 * @psalm-type Options = array{
 *     adapter?: AdapterTypeOrInstance,
 *     options?: array<string, mixed>,
 *     adapter_options?: array<string, mixed>,
 * }
 * @extends AbstractFilter<Options>
 */
class Compress extends AbstractFilter
{
    private const DEFAULT_ADAPTER = 'Gz';

    /**
     * Compression adapter
     *
     * @var AdapterTypeOrInstance
     */
    private string|CompressionAlgorithmInterface $adapter = self::DEFAULT_ADAPTER;

    /**
     * Compression adapter constructor options
     *
     * @var array<string, mixed>
     */
    private array $adapterOptions = [];

    /**
     * Given a string or a compression adapter interface, set the adapter. Otherwise, an iterable will set options
     *
     * @param Options|Traversable<string, mixed>|null|AdapterTypeOrInstance $options
     */
    public function __construct($options = null)
    {
        if ($options instanceof Traversable) {
            /** @psalm-var Options $options */
            $options = ArrayUtils::iteratorToArray($options);
        }

        if (is_string($options) || $options instanceof CompressionAlgorithmInterface) {
            $this->setAdapter($options);

            return;
        }

        if (is_array($options)) {
            $this->setOptions($options);
        }
    }

    /**
     * Set filter state
     *
     * @param  Options|iterable $options
     * @throws Exception\InvalidArgumentException If options is not an array or Traversable.
     * @return $this
     */
    public function setOptions($options)
    {
        if (! is_array($options) && ! $options instanceof Traversable) {
            throw new Exception\InvalidArgumentException(sprintf(
                '"%s" expects an array or Traversable; received "%s"',
                __METHOD__,
                get_debug_type($options)
            ));
        }

        $options = $options instanceof Traversable
            ? ArrayUtils::iteratorToArray($options)
            : $options;

        /** @psalm-var Options $options */

        $adapter = $options['adapter'] ?? null;
        if (is_string($adapter) || $adapter instanceof CompressionAlgorithmInterface) {
            $this->setAdapter($adapter);
        }

        $adapterOptions = $options['options'] ?? [];
        $adapterOptions = $options['adapter_options'] ?? $adapterOptions;

        $this->setAdapterOptions($adapterOptions);

        return $this;
    }

    /**
     * Returns the current adapter, instantiating it if necessary
     *
     * @throws Exception\InvalidArgumentException If the adapter type cannot be resolved.
     */
    public function getAdapter(): CompressionAlgorithmInterface
    {
        if ($this->adapter instanceof CompressionAlgorithmInterface) {
            return $this->adapter;
        }

        $adapterClass = class_exists($this->adapter)
            ? $this->adapter
            : 'Laminas\\Filter\\Compress\\' . ucfirst($this->adapter);

        if (! class_exists($adapterClass) || ! is_a($adapterClass, CompressionAlgorithmInterface::class, true)) {
            throw new Exception\InvalidArgumentException(
                sprintf(
                    'Compression adapter "%s" does not implement %s or is not a valid class',
                    $adapterClass,
                    CompressionAlgorithmInterface::class,
                ),
            );
        }

        $this->adapter = new $adapterClass($this->adapterOptions);

        return $this->adapter;
    }

    /**
     * Retrieve adapter name
     */
    public function getAdapterName(): string
    {
        return $this->getAdapter()->toString();
    }

    /**
     * Sets compression adapter
     *
     * @param AdapterTypeOrInstance $adapter Adapter to use
     * @return $this
     * @throws Exception\InvalidArgumentException
     */
    public function setAdapter(string|CompressionAlgorithmInterface $adapter): self
    {
        $this->adapter = $adapter;

        return $this;
    }

    /**
     * Retrieve adapter options
     *
     * @return array<string, mixed>
     */
    public function getAdapterOptions(): array
    {
        return $this->adapterOptions;
    }

    /**
     * Set adapter options
     *
     * @param array<string, mixed> $options
     * @return $this
     */
    public function setAdapterOptions(array $options): self
    {
        $this->adapterOptions = $options;
        return $this;
    }

    /**
     * Get individual or all options from underlying adapter
     *
     * @return ($option is null ? array : mixed)
     */
    public function getOptions(string|null $option = null): mixed
    {
        $adapter = $this->getAdapter();

        if ($adapter instanceof AbstractCompressionAlgorithm) {
            return $adapter->getOptions($option);
        }

        return is_string($option) ? null : [];
    }

    /**
     * Calls adapter methods
     *
     * @throws Exception\BadMethodCallException
     */
    public function __call(string $method, array $args): mixed
    {
        $adapter = $this->getAdapter();
        if (! method_exists($adapter, $method)) {
            throw new Exception\BadMethodCallException("Unknown method '{$method}'");
        }

        return call_user_func_array([$adapter, $method], $args);
    }

    /**
     * Compresses the content $value with the defined settings
     *
     * @param mixed $value Content to compress
     * @return mixed The compressed content
     * @psalm-return ($value is string ? string : mixed)
     */
    public function filter(mixed $value): mixed
    {
        if (! is_string($value)) {
            return $value;
        }

        return $this->getAdapter()->compress($value);
    }
}
