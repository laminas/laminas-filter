<?php

declare(strict_types=1);

namespace LaminasTest\Filter\Compress;

use Laminas\Filter\Compress\FileExtensionArchiveAdapterResolver;
use Laminas\Filter\Compress\TarAdapter;
use Laminas\Filter\Compress\ZipAdapter;
use Laminas\Filter\Exception\RuntimeException;
use Laminas\Filter\File\FileInformation;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class FileExtensionArchiveAdapterResolverTest extends TestCase
{
    /** @return list<array{0: non-empty-string, 1: class-string}> */
    public static function matchingDataProvider(): array
    {
        return [
            [__DIR__ . '/fixtures/File1.zip', ZipAdapter::class],
            [__DIR__ . '/fixtures/File1.tar.bz2', TarAdapter::class],
            [__DIR__ . '/fixtures/File1.tar.gz', TarAdapter::class],
            [__DIR__ . '/fixtures/File1.tar', TarAdapter::class],
        ];
    }

    /**
     * @param non-empty-string $path
     * @param class-string $class
     */
    #[DataProvider('matchingDataProvider')]
    public function testExpectedAdapterIsReturned(string $path, string $class): void
    {
        $match   = new FileExtensionArchiveAdapterResolver();
        $adapter = $match->resolve(FileInformation::factory($path));

        self::assertInstanceOf($class, $adapter);
    }

    /** @return list<array{0: non-empty-string}> */
    public static function nonMatchingDataProvider(): array
    {
        return [
            [__DIR__ . '/fixtures/directory-to-compress/File1.txt'],
            [__DIR__ . '/fixtures/ZipArchiveWithNoExtension'],
            [__DIR__ . '/fixtures/TarArchiveWithNoExtension'],
        ];
    }

    /**
     * @param non-empty-string $path
     */
    #[DataProvider('nonMatchingDataProvider')]
    public function testInvalidPaths(string $path): void
    {
        $match = new FileExtensionArchiveAdapterResolver();
        $this->expectException(RuntimeException::class);
        $match->resolve(FileInformation::factory($path));
    }
}
