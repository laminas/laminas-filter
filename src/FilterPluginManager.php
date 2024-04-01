<?php

declare(strict_types=1);

namespace Laminas\Filter;

use Laminas\ServiceManager\AbstractPluginManager;
use Laminas\ServiceManager\Exception\InvalidServiceException;
use Laminas\ServiceManager\Factory\InvokableFactory;
use Laminas\ServiceManager\ServiceManager;

use function get_debug_type;
use function is_callable;
use function sprintf;

/**
 * Plugin manager implementation for the filter chain.
 *
 * Enforces that filters retrieved are either callbacks or instances of
 * FilterInterface. Additionally, it registers a number of default filters
 * available, as well as aliases for them.
 *
 * @extends AbstractPluginManager<FilterInterface|callable(mixed): mixed>
 * @psalm-import-type FactoriesConfiguration from ServiceManager
 */
final class FilterPluginManager extends AbstractPluginManager
{
    /** Whether or not to share by default */
    protected bool $sharedByDefault = false;

    /**
     * {@inheritdoc}
     *
     * @psalm-assert FilterInterface|callable(mixed): mixed $instance
     */
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
