<?php

declare(strict_types=1);

namespace LaminasTest\Filter;

use Laminas\Filter\FilterPluginManager;
use Laminas\Filter\FilterPluginManagerFactory;
use Laminas\Filter\Inflector;
use Laminas\Filter\InflectorFactory;
use Laminas\Filter\StringToLower;
use Laminas\Filter\Word\CamelCaseToDash;
use Laminas\ServiceManager\ServiceManager;
use LaminasTest\Filter\TestAsset\InMemoryContainer;
use PHPUnit\Framework\TestCase;

final class InflectorFactoryTest extends TestCase
{
    public function testTheFactoryWillProduceAFilterWithExpectedBehaviourBasedOnOptions(): void
    {
        $container = new InMemoryContainer();
        $plugins   = new FilterPluginManager(new ServiceManager());
        $container->set(FilterPluginManager::class, $plugins);

        $filter = (new InflectorFactory())->__invoke(
            $container,
            'whatever',
            [
                'target' => '/:controller/:action.:suffix',
                'rules'  => [
                    ':controller' => [CamelCaseToDash::class, StringToLower::class],
                    ':action'     => [CamelCaseToDash::class, StringToLower::class],
                    'suffix'      => 'phtml',
                ],
            ],
        );

        self::assertSame(
            '/my-controller/some-action.php',
            $filter->filter([
                'controller' => 'MyController',
                'action'     => 'SomeAction',
                'suffix'     => 'php',
            ]),
        );
    }

    public function testFilterProductionViaPluginManager(): void
    {
        $container = new ServiceManager([
            'factories' => [
                FilterPluginManager::class => FilterPluginManagerFactory::class,
            ],
        ]);
        $plugins   = $container->get(FilterPluginManager::class);
        $filter    = $plugins->build(
            Inflector::class,
            [
                'target' => '/:controller/:action.:suffix',
                'rules'  => [
                    ':controller' => [CamelCaseToDash::class, StringToLower::class],
                    ':action'     => [CamelCaseToDash::class, StringToLower::class],
                    'suffix'      => 'phtml',
                ],
            ],
        );

        self::assertSame(
            '/my-controller/some-action.php',
            $filter->filter([
                'controller' => 'MyController',
                'action'     => 'SomeAction',
                'suffix'     => 'php',
            ]),
        );
    }
}
