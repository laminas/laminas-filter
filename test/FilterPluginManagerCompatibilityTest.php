<?php

declare(strict_types=1);

namespace LaminasTest\Filter;

use Generator;
use Laminas\Filter\Exception\RuntimeException;
use Laminas\Filter\FilterInterface;
use Laminas\Filter\FilterPluginManager;
use Laminas\ServiceManager\ServiceManager;
use Laminas\ServiceManager\Test\CommonPluginManagerTrait;
use PHPUnit\Framework\TestCase;
use ReflectionProperty;

use function in_array;
use function strpos;

class FilterPluginManagerCompatibilityTest extends TestCase
{
    use CommonPluginManagerTrait;

    /**
     * The following aliases are skipped because they are deprecated crypto related filters.
     *
     * These deprecated filters rely on `laminas-crypt` which is not fully compatible with PHP 8.2 and OpenSSL 3+
     */
    private const SKIPPED_ALIASES = [
        'decrypt',
        'encrypt',
        'Decrypt',
        'Encrypt',
        'filedecrypt',
        'fileencrypt',
        'fileDecrypt',
        'fileEncrypt',
        'FileDecrypt',
        'FileEncrypt',
        'Zend\Filter\Decrypt',
        'Zend\Filter\Encrypt',
        'Zend\Filter\File\Decrypt',
        'Zend\Filter\File\Encrypt',
        'zendfilterdecrypt',
        'zendfilterencrypt',
        'zendfilterfiledecrypt',
        'zendfilterfileencrypt',
    ];

    protected static function getPluginManager(): FilterPluginManager
    {
        return new FilterPluginManager(new ServiceManager());
    }

    protected function getV2InvalidPluginException(): string
    {
        return RuntimeException::class;
    }

    protected function getInstanceOf(): string
    {
        return FilterInterface::class;
    }

    /** @return Generator<string, array{0: string, 1: string}> */
    public static function aliasProvider(): Generator
    {
        $pluginManager = self::getPluginManager();
        $r             = new ReflectionProperty($pluginManager, 'aliases');
        $aliases       = $r->getValue($pluginManager);
        self::assertIsArray($aliases);

        foreach ($aliases as $alias => $target) {
            self::assertIsString($alias);
            self::assertIsString($target);
            // Skipping as laminas-i18n is not required by this package
            if (strpos($target, '\\I18n\\') !== false) {
                continue;
            }

            // Skipping as it has required options
            if (strpos($target, 'DataUnitFormatter') !== false) {
                continue;
            }

            if (in_array($alias, self::SKIPPED_ALIASES, true)) {
                continue;
            }

            yield $alias => [$alias, $target];
        }
    }
}
