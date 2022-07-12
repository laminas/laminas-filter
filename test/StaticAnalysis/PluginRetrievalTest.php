<?php

declare(strict_types=1);

namespace LaminasTest\Filter\StaticAnalysis;

use Laminas\Filter\FilterPluginManager;
use Laminas\Filter\StringToUpper;

final class PluginRetrievalTest
{
    private FilterPluginManager $pluginManager;

    public function __construct(FilterPluginManager $pluginManager)
    {
        $this->pluginManager = $pluginManager;
    }

    /** @return mixed */
    public function filterSomethingWithAKnownFilterClass(string $value)
    {
        $plugin = $this->pluginManager->get(StringToUpper::class);

        return $plugin->filter($value);
    }

    /** @return mixed */
    public function filterSomethingWithAnAlias(string $value)
    {
        $plugin = $this->pluginManager->get('stringToUpper');

        return $plugin($value);
    }
}
