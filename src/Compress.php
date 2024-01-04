<?php

declare(strict_types=1);

namespace Laminas\Filter;

use Laminas\Filter\Compress\CompressionAlgorithmInterface;
use Laminas\Stdlib\ArrayUtils;
use Traversable;

use function call_user_func_array;
use function class_exists;
use function get_debug_type;
use function is_array;
use function is_string;
use function method_exists;
use function sprintf;
use function ucfirst;

/**
 * Compresses a given string
 *
 * @psalm-type AdapterType = 'Bz2'|'Gz'|'Lzf'|'Rar'|'Snappy'|'Tar'|'Zip'
 * @psalm-type AdapterTypeOrInstance = Compress\CompressionAlgorithmInterface|AdapterType
 * @psalm-type Options = array{
 *     adapter?: AdapterTypeOrInstance,
 *     options?: array<string, mixed>,
 * }&array<string, mixed>
 * @extends AbstractFilter<Options>
 */
class Compress extends AbstractFilter
{
    /**
     * Compression adapter
     *
     * @var AdapterTypeOrInstance
     */
    protected $adapter = 'Gz';

    /**
     * Compression adapter constructor options
     *
     * @var array<string, mixed>
     */
    protected $adapterOptions = [];

    /**
     * @param Options|Traversable<string, mixed>|null|AdapterTypeOrInstance $options
     */
    public function __construct($options = null)
    {
        if ($options instanceof Traversable) {
            /** @psalm-var Options $options */
            $options = ArrayUtils::iteratorToArray($options);
        }

        if (is_string($options) || $options instanceof Compress\CompressionAlgorithmInterface) {
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
     * @param  Options|Traversable<string, mixed> $options
     * @throws Exception\InvalidArgumentException If options is not an array or Traversable.
     * @return self
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

        foreach ($options as $key => $value) {
            if ($key === 'options') {
                $key = 'adapterOptions';
            }
            $method = 'set' . ucfirst($key);
            if (method_exists($this, $method)) {
                $this->$method($value);
            }
        }
        return $this;
    }

    /**
     * Returns the current adapter, instantiating it if necessary
     *
     * @throws Exception\RuntimeException
     * @throws Exception\InvalidArgumentException
     * @return CompressionAlgorithmInterface
     */
    public function getAdapter()
    {
        if ($this->adapter instanceof Compress\CompressionAlgorithmInterface) {
            return $this->adapter;
        }

        $adapter = $this->adapter;
        $options = $this->getAdapterOptions();
        if (! class_exists($adapter)) {
            $adapter = 'Laminas\\Filter\\Compress\\' . ucfirst($adapter);
            if (! class_exists($adapter)) {
                throw new Exception\RuntimeException(sprintf(
                    '%s unable to load adapter; class "%s" not found',
                    __METHOD__,
                    $this->adapter
                ));
            }
        }

        $instance = new $adapter($options);
        if (! $instance instanceof Compress\CompressionAlgorithmInterface) {
            throw new Exception\InvalidArgumentException(sprintf(
                "Compression adapter '%s' does not implement %s",
                $adapter,
                CompressionAlgorithmInterface::class,
            ));
        }

        $this->adapter = $instance;

        return $this->adapter;
    }

    /**
     * Retrieve adapter name
     *
     * @return string
     */
    public function getAdapterName()
    {
        return $this->getAdapter()->toString();
    }

    /**
     * Sets compression adapter
     *
     * @param AdapterTypeOrInstance $adapter Adapter to use
     * @return self
     * @throws Exception\InvalidArgumentException
     */
    public function setAdapter($adapter)
    {
        if ($adapter instanceof Compress\CompressionAlgorithmInterface) {
            $this->adapter = $adapter;
            return $this;
        }
        if (! is_string($adapter)) {
            throw new Exception\InvalidArgumentException(
                'Invalid adapter provided; must be string or instance of '
                . CompressionAlgorithmInterface::class
            );
        }
        $this->adapter = $adapter;

        return $this;
    }

    /**
     * Retrieve adapter options
     *
     * @return array<string, mixed>
     */
    public function getAdapterOptions()
    {
        return $this->adapterOptions;
    }

    /**
     * Set adapter options
     *
     * @param array<string, mixed> $options
     * @return self
     */
    public function setAdapterOptions(array $options)
    {
        $this->adapterOptions = $options;
        return $this;
    }

    /**
     * Get individual or all options from underlying adapter
     *
     * @param  null|string $option
     * @return mixed
     */
    public function getOptions($option = null)
    {
        $adapter = $this->getAdapter();
        return $adapter->getOptions($option);
    }

    /**
     * Calls adapter methods
     *
     * @param string       $method  Method to call
     * @param string|array $options Options for this method
     * @return mixed
     * @throws Exception\BadMethodCallException
     */
    public function __call($method, $options)
    {
        $adapter = $this->getAdapter();
        if (! method_exists($adapter, $method)) {
            throw new Exception\BadMethodCallException("Unknown method '{$method}'");
        }

        return call_user_func_array([$adapter, $method], $options);
    }

    /**
     * Defined by Laminas\Filter\FilterInterface
     *
     * Compresses the content $value with the defined settings
     *
     * @param  mixed $value Content to compress
     * @return string|mixed The compressed content
     * @psalm-return ($value is string ? string : mixed)
     */
    public function filter($value)
    {
        if (! is_string($value)) {
            return $value;
        }

        return $this->getAdapter()->compress($value);
    }
}
