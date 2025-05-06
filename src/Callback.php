<?php

declare(strict_types=1);

namespace Laminas\Filter;

use Closure;

use function array_unshift;
use function is_callable;

/**
 * @psalm-type Options = array{
 *     callback: callable(mixed): mixed,
 *     callback_params?: array<array-key, mixed>,
 * }
 * @implements FilterInterface<mixed>
 */
final class Callback implements FilterInterface
{
    /** @var Closure(mixed): mixed */
    private readonly Closure $callback;
    private readonly array $arguments;

    /**
     * @param (callable(mixed): mixed)|Options $options
     */
    public function __construct(array|callable $options)
    {
        $callback        = is_callable($options) ? $options : $options['callback'];
        $arguments       = ! is_callable($options) ? $options['callback_params'] ?? [] : [];
        $this->callback  = $callback(...);
        $this->arguments = $arguments;
    }

    public function filter(mixed $value): mixed
    {
        $params = $this->arguments;
        array_unshift($params, $value);

        return ($this->callback)(...$params);
    }

    public function __invoke(mixed $value): mixed
    {
        return $this->filter($value);
    }
}
