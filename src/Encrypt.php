<?php

declare(strict_types=1);

namespace Laminas\Filter;

use Laminas\Stdlib\ArrayUtils;
use Traversable;

use function call_user_func_array;
use function class_exists;
use function is_array;
use function is_numeric;
use function is_string;
use function method_exists;
use function sprintf;
use function substr;
use function ucfirst;

/**
 * Encrypts a given string
 *
 * @deprecated Since 2.24.0. This filter will be removed in 3.0. You are encouraged to use an alternative encryption
 *             library and write your own filter.
 *
 * @psalm-type Options = array{
 *     adapter?: Encrypt\EncryptionAlgorithmInterface,
 *     ...
 * }
 * @extends AbstractFilter<Options>
 * @psalm-suppress DeprecatedInterface
 */
class Encrypt extends AbstractFilter
{
    /**
     * Encryption adapter
     *
     * @param Encrypt\EncryptionAlgorithmInterface
     */
    protected $adapter;

    /**
     * @param string|array|Traversable $options (Optional) Options to set, if null mcrypt is used
     */
    public function __construct($options = null)
    {
        if ($options instanceof Traversable) {
            $options = ArrayUtils::iteratorToArray($options);
        }

        $this->setAdapter($options);
    }

    /**
     * Returns the adapter instance
     *
     * @throws Exception\RuntimeException
     * @throws Exception\InvalidArgumentException
     * @return Encrypt\EncryptionAlgorithmInterface
     */
    public function getAdapterInstance()
    {
        if ($this->adapter instanceof Encrypt\EncryptionAlgorithmInterface) {
            return $this->adapter;
        }

        $adapter = $this->adapter;
        $options = $this->getOptions();
        if (! class_exists($adapter)) {
            $adapter = self::class . '\\' . ucfirst($adapter);
            if (! class_exists($adapter)) {
                throw new Exception\RuntimeException(sprintf(
                    '%s unable to load adapter; class "%s" not found',
                    __METHOD__,
                    $this->adapter
                ));
            }
        }

        $this->adapter = new $adapter($options);
        if (! $this->adapter instanceof Encrypt\EncryptionAlgorithmInterface) {
            throw new Exception\InvalidArgumentException(sprintf(
                'Encryption adapter "%s" does not implement %s\\EncryptionAlgorithmInterface',
                $adapter,
                self::class
            ));
        }
        return $this->adapter;
    }

    /**
     * Returns the name of the set adapter
     *
     * @return string
     */
    public function getAdapter()
    {
        return $this->adapter->toString();
    }

    /**
     * Sets new encryption options
     *
     * @param  string|array $options (Optional) Encryption options
     * @return self
     * @throws Exception\DomainException
     * @throws Exception\InvalidArgumentException
     */
    public function setAdapter($options = null)
    {
        if (is_string($options)) {
            $adapter = $options;
        } elseif (isset($options['adapter'])) {
            $adapter = $options['adapter'];
            unset($options['adapter']);
        } else {
            $adapter = 'BlockCipher';
        }

        if (! is_array($options)) {
            $options = [];
        }

        if (class_exists('Laminas\Filter\Encrypt\\' . ucfirst($adapter))) {
            $adapter = 'Laminas\Filter\Encrypt\\' . ucfirst($adapter);
        } elseif (! class_exists($adapter)) {
            throw new Exception\DomainException(
                sprintf(
                    '%s expects a valid registry class name; received "%s", which did not resolve',
                    __METHOD__,
                    $adapter
                )
            );
        }

        $this->adapter = new $adapter($options);
        if (! $this->adapter instanceof Encrypt\EncryptionAlgorithmInterface) {
            throw new Exception\InvalidArgumentException(
                "Encoding adapter '" . $adapter
                . "' does not implement Laminas\\Filter\\Encrypt\\EncryptionAlgorithmInterface"
            );
        }

        return $this;
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
        $part = substr($method, 0, 3);
        if (($part !== 'get' && $part !== 'set') || ! method_exists($this->adapter, $method)) {
            throw new Exception\BadMethodCallException("Unknown method '{$method}'");
        }

        return call_user_func_array([$this->adapter, $method], $options);
    }

    /**
     * Defined by Laminas\Filter\Filter
     *
     * Encrypts the content $value with the defined settings
     *
     * @param  string $value Content to encrypt
     * @return string The encrypted content
     */
    public function filter($value)
    {
        if (! is_string($value) && ! is_numeric($value)) {
            return $value;
        }

        return $this->adapter->encrypt($value);
    }
}
