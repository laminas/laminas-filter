<?php

declare(strict_types=1);

namespace LaminasTest\Filter\StaticAnalysis;

use Laminas\Filter\FilterInterface;
use Laminas\Filter\FilterPluginManager;
use Laminas\Filter\StringToUpper;

/** @psalm-suppress UnusedClass */
final class PluginRetrieval
{
    public function __construct(private readonly FilterPluginManager $pluginManager)
    {
    }

    public function fqcnPluginTypesAreInferredInGet(): StringToUpper
    {
        return $this->pluginManager->get(StringToUpper::class);
    }

    public function fqcnPluginTypesAreInferredInBuild(): StringToUpper
    {
        return $this->pluginManager->build(StringToUpper::class);
    }

    public function expectCallableOrFilterInterfaceForArbitraryString(): FilterInterface|callable
    {
        return $this->pluginManager->get('something');
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
