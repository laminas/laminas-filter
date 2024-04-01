<?php

declare(strict_types=1);

namespace Laminas\Filter;

use Laminas\ServiceManager\ServiceManager;

/**
 * Implement this interface within Module classes to indicate that your module
 * provides filter configuration for the FilterPluginManager.
 *
 * @psalm-import-type ServiceManagerConfiguration from ServiceManager
 */
interface FilterProviderInterface
{
    /**
     * Provide plugin manager configuration for filters.
     *
     * @return ServiceManagerConfiguration
     */
    public function getFilterConfig(): array;
}
