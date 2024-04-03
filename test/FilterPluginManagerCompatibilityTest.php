<?php

declare(strict_types=1);

namespace LaminasTest\Filter;

use Generator;
use Laminas\Filter\ConfigProvider;
use Laminas\Filter\FilterPluginManager;
use Laminas\ServiceManager\Exception\InvalidServiceException;
use PHPUnit\Framework\TestCase;
use stdClass;
use Throwable;

use function assert;
use function class_exists;
use function strpos;

class FilterPluginManagerCompatibilityTest extends TestCase
{
    protected static function getPluginManager(): FilterPluginManager
    {
        return CreatePluginManager::withDefaults();
    }

    /** @return Generator<string, array{0: string, 1: class-string}> */
    public static function aliasProvider(): Generator
    {
        $aliases = (new ConfigProvider())()['filters']['aliases'] ?? [];

        foreach ($aliases as $alias => $target) {
            // Skipping as it has required options
            if (strpos($target, 'DataUnitFormatter') !== false) {
                continue;
            }

            assert(class_exists($target));

            yield $alias => [$alias, $target];
        }
    }

    /**
     * @param class-string $expected
     * @dataProvider aliasProvider
     */
    public function testPluginAliasesResolve(string $alias, string $expected): void
    {
        self::assertInstanceOf(
            $expected,
            self::getPluginManager()->get($alias),
            "Alias '$alias' does not resolve'",
        );
    }

    public function testLoadingInvalidElementRaisesException(): void
    {
        $manager = self::getPluginManager();
        $manager->configure([
            'factories' => [
                'test' => static fn(): stdClass => new stdClass(),
            ],
        ]);
        $this->expectException($this->getServiceNotFoundException());
        $manager->get('test');
    }

    /** @return class-string<Throwable> */
    protected function getServiceNotFoundException(): string
    {
        return InvalidServiceException::class;
    }

    public function testRegisteringInvalidElementRaisesException(): void
    {
        $manager = self::getPluginManager();
        $this->expectException($this->getServiceNotFoundException());
        /** @psalm-suppress InvalidArgument - Because we are testing an invalid argument */
        $manager->setService('test', new stdClass());
    }
}
