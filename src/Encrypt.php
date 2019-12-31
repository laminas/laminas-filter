<?php

/**
 * @see       https://github.com/laminas/laminas-filter for the canonical source repository
 * @copyright https://github.com/laminas/laminas-filter/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-filter/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\Filter;

use Laminas\Stdlib\ArrayUtils;
use Traversable;

/**
 * Encrypts a given string
 */
class Encrypt extends AbstractFilter
{
    /**
     * Encryption adapter
     */
    protected $adapter;

    /**
     * Class constructor
     *
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

        if (!is_array($options)) {
            $options = array();
        }

        if (class_exists('Laminas\Filter\Encrypt\\' . ucfirst($adapter))) {
            $adapter = 'Laminas\Filter\Encrypt\\' . ucfirst($adapter);
        } elseif (!class_exists($adapter)) {
            throw new Exception\DomainException(
                sprintf('%s expects a valid registry class name; received "%s", which did not resolve',
                    __METHOD__,
                    $adapter
            ));
        }

        $this->adapter = new $adapter($options);
        if (!$this->adapter instanceof Encrypt\EncryptionAlgorithmInterface) {
            throw new Exception\InvalidArgumentException(
                "Encoding adapter '" . $adapter
                . "' does not implement Laminas\\Filter\\Encrypt\\EncryptionAlgorithmInterface");
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
        if ((($part != 'get') && ($part != 'set')) || !method_exists($this->adapter, $method)) {
            throw new Exception\BadMethodCallException("Unknown method '{$method}'");
        }

        return call_user_func_array(array($this->adapter, $method), $options);
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
        return $this->adapter->encrypt($value);
    }
}
