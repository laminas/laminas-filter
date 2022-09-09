<?php

declare(strict_types=1);

namespace LaminasTest\Filter;

use Laminas\Filter\Boolean;
use Laminas\Filter\FilterInterface;
use Laminas\Filter\FilterPluginManager;
use Laminas\Filter\FilterPluginManagerFactory;
use Laminas\ServiceManager\ServiceLocatorInterface;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Psr\Container\ContainerInterface;
use ReflectionObject;

use function method_exists;

class FilterPluginManagerFactoryTest extends TestCase
{
    use ProphecyTrait;

    public function testFactoryReturnsPluginManager(): void
    {
        $container = $this->prophesize(ContainerInterface::class)->reveal();
        $factory   = new FilterPluginManagerFactory();

        $filters = $factory($container, FilterPluginManagerFactory::class);
        $this->assertInstanceOf(FilterPluginManager::class, $filters);

        if (method_exists($filters, 'configure')) {
            // laminas-servicemanager v3
            $r = new ReflectionObject($filters);
            $p = $r->getProperty('creationContext');
            $p->setAccessible(true);
            $this->assertSame($container, $p->getValue($filters));
        } else {
            // laminas-servicemanager v2
            $this->assertSame($container, $filters->getServiceLocator());
        }
    }

    /**
     * @depends testFactoryReturnsPluginManager
     */
    public function testFactoryConfiguresPluginManagerUnderContainerInterop(): void
    {
        $container = $this->prophesize(ContainerInterface::class)->reveal();
        $filter    = static fn($value) => $value;

        $factory = new FilterPluginManagerFactory();
        $filters = $factory($container, FilterPluginManagerFactory::class, [
            'services' => [
                'test' => $filter,
            ],
        ]);
        $this->assertSame($filter, $filters->get('test'));
    }

    /**
     * @depends testFactoryReturnsPluginManager
     */
    public function testFactoryConfiguresPluginManagerUnderServiceManagerV2()
    {
        $container = $this->prophesize(ServiceLocatorInterface::class);
        $container->willImplement(ContainerInterface::class);

        $filter = static fn($value) => $value;

        $factory = new FilterPluginManagerFactory();
        $factory->setCreationOptions([
            'services' => [
                'test' => $filter,
            ],
        ]);

        $filters = $factory->createService($container->reveal());
        $this->assertSame($filter, $filters->get('test'));
    }

    public function testConfiguresFilterServicesWhenFound(): void
    {
        $filter = $this->prophesize(FilterInterface::class)->reveal();
        $config = [
            'filters' => [
                'aliases'   => [
                    'test' => Boolean::class,
                ],
                'factories' => [
                    'test-too' => static fn($container) => $filter,
                ],
            ],
        ];

        $container = $this->prophesize(ServiceLocatorInterface::class);
        $container->willImplement(ContainerInterface::class);

        $container->has('ServiceListener')->willReturn(false);
        $container->has('config')->willReturn(true);
        $container->get('config')->willReturn($config);

        $factory = new FilterPluginManagerFactory();
        $filters = $factory($container->reveal(), 'FilterManager');

        $this->assertInstanceOf(FilterPluginManager::class, $filters);
        $this->assertTrue($filters->has('test'));
        $this->assertInstanceOf(Boolean::class, $filters->get('test'));
        $this->assertTrue($filters->has('test-too'));
        $this->assertSame($filter, $filters->get('test-too'));
    }

    public function testDoesNotConfigureFilterServicesWhenServiceListenerPresent(): void
    {
        $filter = $this->prophesize(FilterInterface::class)->reveal();
        $config = [
            'filters' => [
                'aliases'   => [
                    'test' => Boolean::class,
                ],
                'factories' => [
                    'test-too' => static fn($container) => $filter,
                ],
            ],
        ];

        $container = $this->prophesize(ServiceLocatorInterface::class);
        $container->willImplement(ContainerInterface::class);

        $container->has('ServiceListener')->willReturn(true);
        $container->has('config')->shouldNotBeCalled();
        $container->get('config')->shouldNotBeCalled();

        $factory = new FilterPluginManagerFactory();
        $filters = $factory($container->reveal(), 'FilterManager');

        $this->assertInstanceOf(FilterPluginManager::class, $filters);
        $this->assertFalse($filters->has('test'));
        $this->assertFalse($filters->has('test-too'));
    }

    public function testDoesNotConfigureFilterServicesWhenConfigServiceNotPresent(): void
    {
        $container = $this->prophesize(ServiceLocatorInterface::class);
        $container->willImplement(ContainerInterface::class);

        $container->has('ServiceListener')->willReturn(false);
        $container->has('config')->willReturn(false);
        $container->get('config')->shouldNotBeCalled();

        $factory = new FilterPluginManagerFactory();
        $filters = $factory($container->reveal(), 'FilterManager');

        $this->assertInstanceOf(FilterPluginManager::class, $filters);
    }

    public function testDoesNotConfigureFilterServicesWhenConfigServiceDoesNotContainFiltersConfig(): void
    {
        $container = $this->prophesize(ServiceLocatorInterface::class);
        $container->willImplement(ContainerInterface::class);

        $container->has('ServiceListener')->willReturn(false);
        $container->has('config')->willReturn(true);
        $container->get('config')->willReturn(['foo' => 'bar']);

        $factory = new FilterPluginManagerFactory();
        $filters = $factory($container->reveal(), 'FilterManager');

        $this->assertInstanceOf(FilterPluginManager::class, $filters);
        $this->assertFalse($filters->has('foo'));
    }
}
