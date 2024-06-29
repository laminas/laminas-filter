<?php

declare(strict_types=1);

namespace Laminas\Filter;

use Traversable;

use function array_unshift;
use function call_user_func_array;
use function class_exists;
use function is_callable;
use function is_string;

/**
 * @psalm-type Options = array{
 *     callback?: callable,
 *     callback_params?: array,
 *     ...
 * }
 * @extends AbstractFilter<Options>
 * @final
 */
class Callback extends AbstractFilter
{
    /** @var array */
    protected $options = [
        'callback'        => null,
        'callback_params' => [],
    ];

    /**
     * @param callable|array|string|Traversable $callbackOrOptions
     * @param array $callbackParams
     */
    public function __construct($callbackOrOptions = [], $callbackParams = [])
    {
        if (is_callable($callbackOrOptions) || is_string($callbackOrOptions)) {
            $this->setCallback($callbackOrOptions);
            $this->setCallbackParams($callbackParams);
        } else {
            $this->setOptions($callbackOrOptions);
        }
    }

    /**
     * Sets a new callback for this filter
     *
     * @deprecated since 2.37.0 - All option setters and getters will be removed in 3.0
     *
     * @param  callable $callback
     * @return self
     * @throws Exception\InvalidArgumentException
     */
    public function setCallback($callback)
    {
        if (is_string($callback) && class_exists($callback)) {
            $callback = new $callback();
        }

        if (! is_callable($callback)) {
            throw new Exception\InvalidArgumentException(
                'Invalid parameter for callback: must be callable'
            );
        }

        $this->options['callback'] = $callback;
        return $this;
    }

    /**
     * Returns the set callback
     *
     * @deprecated since 2.37.0 - All option setters and getters will be removed in 3.0
     *
     * @return callable
     */
    public function getCallback()
    {
        return $this->options['callback'];
    }

    /**
     * Sets parameters for the callback
     *
     * @deprecated since 2.37.0 - All option setters and getters will be removed in 3.0
     *
     * @param  array $params
     * @return self
     */
    public function setCallbackParams($params)
    {
        $this->options['callback_params'] = (array) $params;
        return $this;
    }

    /**
     * Get parameters for the callback
     *
     * @deprecated since 2.37.0 - All option setters and getters will be removed in 3.0
     *
     * @return array
     */
    public function getCallbackParams()
    {
        return $this->options['callback_params'];
    }

    /**
     * Calls the filter per callback
     *
     * @param  mixed $value Options for the set callable
     * @return mixed Result from the filter which was called
     */
    public function filter($value)
    {
        $params = (array) $this->options['callback_params'];
        array_unshift($params, $value);

        return call_user_func_array($this->options['callback'], $params);
    }
}
