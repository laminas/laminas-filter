<?php

declare(strict_types=1);

namespace LaminasTest\Filter\Compress;

use Laminas\Filter\Compress\ZipAdapter;
use Laminas\Filter\Exception\InvalidArgumentException;
use Laminas\Filter\Exception\RuntimeException;
use PHPUnit\Framework\Attributes\WithoutErrorHandler;
use PHPUnit\Framework\TestCase;

use function chmod;
use function extension_loaded;
use function file_get_contents;
use function mkdir;
use function restore_error_handler;
use function set_error_handler;
use function sprintf;
use function sys_get_temp_dir;
use function uniqid;

use const DIRECTORY_SEPARATOR;
use const E_WARNING;

final class ZipAdapterTest extends TestCase
{
    /** @var non-empty-string */
    private string $tmp;

    public function setUp(): void
    {
        if (! extension_loaded('zip')) {
            self::markTestSkipped('This adapter needs the zip extension');
        }

        $this->tmp = sprintf('%s%s%s', sys_get_temp_dir(), DIRECTORY_SEPARATOR, uniqid('laminas_'));
        mkdir($this->tmp);
    }

    public function tearDown(): void
    {
        TmpDirectory::cleanUp($this->tmp);
    }

    public function testFilesCanBeCompressedToAnArchive(): void
    {
        $archive    = $this->tmp . '/archive.zip';
        $expectFile = $this->tmp . '/File1.txt';
        $sourceFile = __DIR__ . '/fixtures/directory-to-compress/File1.txt';

        $adapter = new ZipAdapter();

        self::assertFileDoesNotExist($archive);
        $adapter->archiveFile($archive, $sourceFile);
        self::assertFileExists($archive);

        self::assertFileDoesNotExist($expectFile);
        $adapter->expandArchive($archive, $this->tmp);
        self::assertFileExists($expectFile);

        self::assertSame(
            file_get_contents($sourceFile),
            file_get_contents($expectFile),
        );
    }

    public function testStringsCanBeCompressedToAFile(): void
    {
        $archive    = $this->tmp . '/archive.zip';
        $expectFile = $this->tmp . '/SomeFile.txt';
        $content    = 'Some Contents';

        $adapter = new ZipAdapter();

        self::assertFileDoesNotExist($archive);
        $adapter->archiveStringToFile($archive, 'SomeFile.txt', $content);
        self::assertFileExists($archive);

        self::assertFileDoesNotExist($expectFile);
        $adapter->expandArchive($archive, $this->tmp);
        self::assertFileExists($expectFile);

        self::assertSame(
            $content,
            file_get_contents($expectFile),
        );
    }

    public function testDirectoryCompression(): void
    {
        $archive = $this->tmp . '/archive.zip';
        $source  = __DIR__ . '/fixtures/directory-to-compress';

        $adapter = new ZipAdapter();

        self::assertFileDoesNotExist($archive);
        $adapter->archiveDirectoryContents($archive, $source);
        self::assertFileExists($archive);

        $adapter->expandArchive($archive, $this->tmp);

        self::assertFileExists($this->tmp . '/File1.txt');
        self::assertFileExists($this->tmp . '/File2.txt');
        self::assertFileExists($this->tmp . '/nested/File3.txt');
    }

    public function testCompressingNonExistentDirectory(): void
    {
        $archive = $this->tmp . '/archive.zip';
        $adapter = new ZipAdapter();
        try {
            $adapter->archiveDirectoryContents($archive, 'Not-Found');
            self::fail('An exception was expected');
        } catch (InvalidArgumentException $e) {
            self::assertSame('The directory argument is not a directory', $e->getMessage());
        } finally {
            self::assertFileDoesNotExist($archive);
        }
    }

    #[WithoutErrorHandler]
    public function testCompressingANonExistentFile(): void
    {
        // ZipArchive emits warnings for non-existent files too so we will swallow warnings here
        set_error_handler(
            static fn (int $_a, string $_b): bool => true, // phpcs:ignore
            E_WARNING,
        );

        $archive = $this->tmp . '/archive.zip';
        $adapter = new ZipAdapter();
        try {
            $adapter->archiveFile($archive, 'Not-Found.txt');
            self::fail('An exception was expected');
        } catch (RuntimeException $e) {
            self::assertSame('Failed to add the file Not-Found.txt to the archive', $e->getMessage());
        } finally {
            self::assertFileDoesNotExist($archive);
            restore_error_handler();
        }
    }

    public function testCompressionToAnUnWritableDirectory(): void
    {
        $dir = $this->tmp . '/un-writable';
        mkdir($dir);
        chmod($dir, 0400);
        $archive = $dir . '/archive.zip';
        $adapter = new ZipAdapter();
        try {
            $adapter->archiveFile($archive, __DIR__ . '/fixtures/directory-to-compress/File1.txt');
            self::fail('An exception was expected');
        } catch (RuntimeException $e) {
            self::assertStringContainsString('The archive could not be opened', $e->getMessage());
        } finally {
            chmod($dir, 0700);
            self::assertFileDoesNotExist($archive);
        }
    }

    #[WithoutErrorHandler]
    public function testDecompressionToAnUnWritableTargetDirectory(): void
    {
        // ZipArchive emits warnings here
        set_error_handler(
            static fn (int $_a, string $_b): bool => true, // phpcs:ignore
            E_WARNING,
        );

        $dir = $this->tmp . '/un-writable';
        mkdir($dir);
        chmod($dir, 0400);
        $archive = $this->tmp . '/archive.zip';
        $adapter = new ZipAdapter();
        $adapter->archiveFile($archive, __DIR__ . '/fixtures/directory-to-compress/File1.txt');

        try {
            $adapter->expandArchive($archive, $dir);
            self::fail('An exception was expected');
        } catch (RuntimeException $e) {
            self::assertStringContainsString('Failed to extract archive to the target directory', $e->getMessage());
        } finally {
            chmod($dir, 0700);
            restore_error_handler();
        }
    }
}
