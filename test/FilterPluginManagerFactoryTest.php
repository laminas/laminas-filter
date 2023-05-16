<?php

declare(strict_types=1);

namespace LaminasTest\Filter;

use Laminas\Filter\Boolean;
use Laminas\Filter\FilterInterface;
use Laminas\Filter\FilterPluginManager;
use Laminas\Filter\FilterPluginManagerFactory;
use Laminas\ServiceManager\ServiceLocatorInterface;
use PHPUnit\Framework\Attributes\Depends;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use ReflectionObject;

use function method_exists;

class FilterPluginManagerFactoryTest extends TestCase
{
    public function testFactoryReturnsPluginManager(): void
    {
        $container = $this->createMock(ContainerInterface::class);
        $factory   = new FilterPluginManagerFactory();

        $filters = $factory($container, FilterPluginManagerFactory::class);
        self::assertInstanceOf(FilterPluginManager::class, $filters);

        if (method_exists($filters, 'configure')) {
            // laminas-servicemanager v3
            $r = new ReflectionObject($filters);
            $p = $r->getProperty('creationContext');
            self::assertSame($container, $p->getValue($filters));
        } else {
            // laminas-servicemanager v2
            self::assertSame($container, $filters->getServiceLocator());
        }
    }

    #[Depends('testFactoryReturnsPluginManager')]
    public function testFactoryConfiguresPluginManagerUnderContainerInterop(): void
    {
        $container = $this->createMock(ContainerInterface::class);
        $filter    = static fn($value) => $value;

        $factory = new FilterPluginManagerFactory();
        $filters = $factory($container, FilterPluginManagerFactory::class, [
            'services' => [
                'test' => $filter,
            ],
        ]);
        self::assertSame($filter, $filters->get('test'));
    }

    #[Depends('testFactoryReturnsPluginManager')]
    public function testFactoryConfiguresPluginManagerUnderServiceManagerV2()
    {
        $container = $this->createMock(ServiceLocatorInterface::class);

        $filter = static fn($value) => $value;

        $factory = new FilterPluginManagerFactory();
        $factory->setCreationOptions([
            'services' => [
                'test' => $filter,
            ],
        ]);

        $filters = $factory->createService($container);
        self::assertSame($filter, $filters->get('test'));
    }

    public function testConfiguresFilterServicesWhenFound(): void
    {
        $filter = $this->createMock(FilterInterface::class);
        $config = [
            'filters' => [
                'aliases'   => [
                    'test' => Boolean::class,
                ],
                'factories' => [
                    'test-too' => static fn($container): MockObject => $filter,
                ],
            ],
        ];

        $container = $this->createMock(ServiceLocatorInterface::class);
        $container->expects(self::atLeast(2))
            ->method('has')
            ->willReturnMap([
                ['ServiceListener', false],
                ['config', true],
            ]);
        $container->expects(self::once())
            ->method('get')
            ->with('config')
            ->willReturn($config);

        $factory = new FilterPluginManagerFactory();
        $filters = $factory($container, 'FilterManager');

        self::assertInstanceOf(FilterPluginManager::class, $filters);
        self::assertTrue($filters->has('test'));
        self::assertInstanceOf(Boolean::class, $filters->get('test'));
        self::assertTrue($filters->has('test-too'));
        self::assertSame($filter, $filters->get('test-too'));
    }

    public function testDoesNotConfigureFilterServicesWhenServiceListenerPresent(): void
    {
        $container = $this->createMock(ServiceLocatorInterface::class);

        $container->expects(self::once())
            ->method('has')
            ->with('ServiceListener')
            ->willReturn(true);

        $factory = new FilterPluginManagerFactory();
        $filters = $factory($container, 'FilterManager');

        self::assertInstanceOf(FilterPluginManager::class, $filters);
        self::assertFalse($filters->has('test'));
        self::assertFalse($filters->has('test-too'));
    }

    public function testDoesNotConfigureFilterServicesWhenConfigServiceNotPresent(): void
    {
        $container = $this->createMock(ServiceLocatorInterface::class);

        $container->expects(self::exactly(2))
            ->method('has')
            ->willReturnMap([
                ['ServiceListener', false],
                ['config', false],
            ]);

        $container->expects(self::never())->method('get');

        $factory = new FilterPluginManagerFactory();
        $filters = $factory($container, 'FilterManager');

        self::assertInstanceOf(FilterPluginManager::class, $filters);
    }

    public function testDoesNotConfigureFilterServicesWhenConfigServiceDoesNotContainFiltersConfig(): void
    {
        $container = $this->createMock(ServiceLocatorInterface::class);

        $container->expects(self::exactly(2))
            ->method('has')
            ->willReturnMap([
                ['ServiceListener', false],
                ['config', true],
            ]);

        $container->expects(self::once())
            ->method('get')
            ->with('config')
            ->willReturn(['foo' => 'bar']);

        $factory = new FilterPluginManagerFactory();
        $filters = $factory($container, 'FilterManager');

        self::assertInstanceOf(FilterPluginManager::class, $filters);
        self::assertFalse($filters->has('foo'));
    }
}
