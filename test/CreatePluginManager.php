<?php

declare(strict_types=1);

namespace LaminasTest\Filter;

use Laminas\Filter\FilterPluginManager;
use Laminas\ServiceManager\ServiceManager;

final class CreatePluginManager
{
    public static function withDefaults(): FilterPluginManager
    {
        return new FilterPluginManager(
            new ServiceManager(),
            []
        );
    }
}
