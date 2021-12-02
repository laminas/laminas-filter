<?php

declare(strict_types=1);

namespace LaminasTest\Filter;

use Laminas\Filter\Callback;
use Laminas\Filter\Digits;
use Laminas\Filter\FilterPluginManager;
use Laminas\Filter\HtmlEntities;
use Laminas\Filter\StaticFilter;
use Laminas\ServiceManager\Exception;
use Laminas\ServiceManager\ServiceManager;
use PHPUnit\Framework\TestCase;

use const ENT_COMPAT;
use const ENT_QUOTES;

class StaticFilterTest extends TestCase
{
    /**
     * Resets the default namespaces
     */
    public function tearDown(): void
    {
        StaticFilter::setPluginManager(null);
    }

    public function testUsesFilterPluginManagerByDefault()
    {
        $plugins = StaticFilter::getPluginManager();
        $this->assertInstanceOf(FilterPluginManager::class, $plugins);
    }

    public function testCanSpecifyCustomPluginManager()
    {
        $plugins = new FilterPluginManager(new ServiceManager());
        StaticFilter::setPluginManager($plugins);
        $this->assertSame($plugins, StaticFilter::getPluginManager());
    }

    public function testCanResetPluginManagerByPassingNull()
    {
        $plugins = new FilterPluginManager(new ServiceManager());
        StaticFilter::setPluginManager($plugins);
        $this->assertSame($plugins, StaticFilter::getPluginManager());
        StaticFilter::setPluginManager(null);
        $registered = StaticFilter::getPluginManager();
        $this->assertNotSame($plugins, $registered);
        $this->assertInstanceOf(FilterPluginManager::class, $registered);
    }

    /**
     * Ensures that we can call the static method execute()
     * to instantiate a named validator by its class basename
     * and it returns the result of filter() with the input.
     */
    public function testStaticFactory()
    {
        $filteredValue = StaticFilter::execute('1a2b3c4d', Digits::class);
        $this->assertEquals('1234', $filteredValue);
    }

    /**
     * Ensures that a validator with constructor arguments can be called
     * with the static method get().
     */
    public function testStaticFactoryWithConstructorArguments()
    {
        // Test HtmlEntities with one ctor argument.
        $filteredValue = StaticFilter::execute('"O\'Reilly"', HtmlEntities::class, ['quotestyle' => ENT_COMPAT]);
        $this->assertEquals('&quot;O\'Reilly&quot;', $filteredValue);

        // Test HtmlEntities with a different ctor argument,
        // and make sure it gives the correct response
        // so we know it passed the arg to the ctor.
        $filteredValue = StaticFilter::execute('"O\'Reilly"', HtmlEntities::class, ['quotestyle' => ENT_QUOTES]);
        $this->assertEquals('&quot;O&#039;Reilly&quot;', $filteredValue);
    }

    /**
     * Ensures that if we specify a validator class basename that doesn't
     * exist in the namespace, get() throws an exception.
     *
     * Refactored to conform with Laminas-2724.
     *
     * @group  Laminas-2724
     */
    public function testStaticFactoryClassNotFound()
    {
        $this->expectException(Exception\ExceptionInterface::class);
        StaticFilter::execute('1234', 'UnknownFilter');
    }

    public function testUsesDifferentConfigurationOnEachRequest()
    {
        $first  = StaticFilter::execute('foo', Callback::class, [
            'callback' => function ($value) {
                return 'FOO';
            },
        ]);
        $second = StaticFilter::execute('foo', Callback::class, [
            'callback' => function ($value) {
                return 'BAR';
            },
        ]);
        $this->assertNotSame($first, $second);
        $this->assertEquals('FOO', $first);
        $this->assertEquals('BAR', $second);
    }
}
