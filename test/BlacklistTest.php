<?php

declare(strict_types=1);

namespace LaminasTest\Filter;

use Laminas\Filter\Blacklist as BlacklistFilter;
use Laminas\Filter\DenyList;
use Laminas\Filter\FilterPluginManager;
use Laminas\ServiceManager\ServiceManager;
use PHPUnit\Framework\TestCase;

class BlacklistTest extends TestCase
{
    public function testConstructor(): void
    {
        $filter = new BlacklistFilter();
        $this->assertInstanceOf(DenyList::class, $filter);
    }

    public function testWithPluginManager(): void
    {
        $pluginManager = new FilterPluginManager(new ServiceManager());
        $filter        = $pluginManager->get('blacklist');

        $this->assertInstanceOf(DenyList::class, $filter);
    }
}
