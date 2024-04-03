<?php

declare(strict_types=1);

namespace Laminas\Filter;

use Laminas\ServiceManager\AbstractPluginManager;
use Laminas\ServiceManager\Exception\InvalidServiceException;

use function get_debug_type;
use function is_callable;
use function sprintf;

/**
 * Plugin manager implementation for filters
 *
 * Enforces that filters retrieved are either callbacks or instances of FilterInterface.
 *
 * @psalm-type InstanceType = FilterInterface|callable(mixed): mixed
 * @extends AbstractPluginManager<InstanceType>
 */
final class FilterPluginManager extends AbstractPluginManager
{
    /** Filter instances are never shared */
    protected bool $sharedByDefault = false;

    /** Generally speaking, filters can be constructed without arguments */
    protected bool $autoAddInvokableClass = true;

    /** @inheritDoc */
    public function validate(mixed $instance): void
    {
        if ($instance instanceof FilterInterface) {
            return;
        }

        if (is_callable($instance)) {
            return;
        }

        throw new InvalidServiceException(sprintf(
            'Plugin of type %s is invalid; must implement %s\FilterInterface or be callable',
            get_debug_type($instance),
            __NAMESPACE__
        ));
    }
}
