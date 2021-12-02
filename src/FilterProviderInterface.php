<?php

declare(strict_types=1);

namespace Laminas\Filter;

/**
 * Implement this interface within Module classes to indicate that your module
 * provides filter configuration for the FilterPluginManager.
 */
interface FilterProviderInterface
{
    /**
     * Provide plugin manager configuration for filters.
     *
     * @return array
     */
    public function getFilterConfig();
}
