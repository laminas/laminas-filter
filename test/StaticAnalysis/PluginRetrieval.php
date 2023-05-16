<?php

declare(strict_types=1);

namespace LaminasTest\Filter\StaticAnalysis;

use Laminas\Filter\FilterPluginManager;
use Laminas\Filter\StringToUpper;

/** @psalm-suppress UnusedClass */
final class PluginRetrieval
{
    public function __construct(private FilterPluginManager $pluginManager)
    {
    }

    public function filterSomethingWithAKnownFilterClass(string $value): string
    {
        $plugin = $this->pluginManager->get(StringToUpper::class);

        return $plugin->filter($value);
    }

    public function filterSomethingWithAnAlias(string $value): mixed
    {
        $plugin = $this->pluginManager->get('stringToUpper');

        return $plugin($value);
    }
}
