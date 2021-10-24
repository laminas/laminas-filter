<?php

namespace LaminasTest\Filter;

use Laminas\Filter\AllowList;
use Laminas\Filter\Whitelist as WhitelistFilter;
use Laminas\Filter\FilterPluginManager;
use Laminas\ServiceManager\ServiceManager;
use PHPUnit\Framework\TestCase;

class WhitelistTest extends TestCase
{
    public function testRaisesNoticeOnInstantiation(): void
    {
        $this->expectDeprecation();
        new WhitelistFilter();
    }

    public function testWithPluginManager(): void
    {
        $pluginManager = new FilterPluginManager(new ServiceManager());
        $filter = $pluginManager->get('whitelist');

        $this->assertInstanceOf(AllowList::class, $filter);
    }
}
