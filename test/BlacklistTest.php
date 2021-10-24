<?php

namespace LaminasTest\Filter;

use Laminas\Filter\Blacklist as BlacklistFilter;
use Laminas\Filter\DenyList;
use Laminas\Filter\FilterPluginManager;
use Laminas\ServiceManager\ServiceManager;
use PHPUnit\Framework\TestCase;

class BlacklistTest extends TestCase
{
    public function testRaisesNoticeOnInstantiation(): void
    {
        $this->expectDeprecation();
        new BlacklistFilter();
    }

    public function testWithPluginManager(): void
    {
        $pluginManager = new FilterPluginManager(new ServiceManager());
        $filter = $pluginManager->get('blacklist');

        $this->assertInstanceOf(DenyList::class, $filter);
    }
}
