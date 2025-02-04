<?php

declare(strict_types=1);

namespace LaminasTest\Filter;

use Laminas\Diactoros\UploadedFile;
use Laminas\Filter\Compress\TarAdapter;
use Laminas\Filter\Compress\ZipAdapter;
use Laminas\Filter\CompressToArchive;
use Laminas\Filter\Exception\RuntimeException;
use LaminasTest\Filter\Compress\TmpDirectory;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

use function file_get_contents;
use function filesize;
use function is_dir;
use function mkdir;
use function sys_get_temp_dir;

use const UPLOAD_ERR_OK;

/** @psalm-import-type Options from CompressToArchive */
class CompressToArchiveTest extends TestCase
{
    /** @var non-empty-string */
    private string $tmp;

    protected function setUp(): void
    {
        $this->tmp = sys_get_temp_dir() . '/LaminasCompressToArchiveTest';
        if (! is_dir($this->tmp)) {
            mkdir($this->tmp);
        }
    }

    protected function tearDown(): void
    {
        TmpDirectory::cleanUp($this->tmp);
    }

    /** @return array<string, array{0: Options}> */
    public static function optionsProvider(): array
    {
        $basePath = sys_get_temp_dir() . '/LaminasCompressToArchiveTest';
        if (! is_dir($basePath)) {
            mkdir($basePath);
        }

        return [
            'Zip as String' => [
                [
                    'adapter' => 'zip',
                    'archive' => $basePath . '/archive.zip',
                ],
            ],
            'Zip Instance'  => [
                [
                    'adapter' => new ZipAdapter(),
                    'archive' => $basePath . '/archive.zip',
                ],
            ],
            'Tar as String' => [
                [
                    'adapter' => 'tar',
                    'archive' => $basePath . '/archive.tar',
                ],
            ],
            'Tar Instance'  => [
                [
                    'adapter' => new TarAdapter(),
                    'archive' => $basePath . '/archive.tar',
                ],
            ],
        ];
    }

    /** @param Options $options */
    #[DataProvider('optionsProvider')]
    public function testAFilePathWillBeCompressedToTheConfiguredArchive(array $options): void
    {
        $filter = new CompressToArchive($options);
        $path   = __DIR__ . '/Compress/fixtures/directory-to-compress/File1.txt';

        self::assertFileDoesNotExist($options['archive']);
        $result = $filter->__invoke($path);
        self::assertFileExists($options['archive']);
        self::assertSame($options['archive'], $result);
    }

    /** @param Options $options */
    #[DataProvider('optionsProvider')]
    public function testADirectoryWillBeCompressedToTheConfiguredArchive(array $options): void
    {
        $filter = new CompressToArchive($options);
        $path   = __DIR__ . '/Compress/fixtures/directory-to-compress';

        self::assertFileDoesNotExist($options['archive']);
        $result = $filter->__invoke($path);
        self::assertFileExists($options['archive']);
        self::assertSame($options['archive'], $result);
    }

    /** @param Options $options */
    #[DataProvider('optionsProvider')]
    public function testAPHPFileArrayWillYieldACompressedArchive(array $options): void
    {
        $filter = new CompressToArchive($options);
        $path   = __DIR__ . '/Compress/fixtures/directory-to-compress/File1.txt';
        $files  = [
            'error'    => UPLOAD_ERR_OK,
            'tmp_name' => $path,
            'name'     => 'Foo.txt',
            'type'     => 'text/plain',
            'size'     => 0,
        ];

        self::assertFileDoesNotExist($options['archive']);
        $result = $filter->__invoke($files);
        self::assertFileExists($options['archive']);
        self::assertSame($options['archive'], $result);
    }

    /** @param Options $options */
    #[DataProvider('optionsProvider')]
    public function testPsrUploadedFileCompression(array $options): void
    {
        $filter = new CompressToArchive($options);
        $path   = __DIR__ . '/Compress/fixtures/directory-to-compress/File1.txt';
        $size   = filesize($path);
        self::assertIsInt($size);
        $upload = new UploadedFile(
            $path,
            $size,
            UPLOAD_ERR_OK,
            'Foo.txt',
            'text/plain',
        );

        self::assertFileDoesNotExist($options['archive']);
        $result = $filter->__invoke($upload);
        self::assertFileExists($options['archive']);
        self::assertSame($options['archive'], $result);
    }

    public function testArbitraryStringsWillBeCompressedInAnArchivedFile(): void
    {
        $adapter = new ZipAdapter();
        $options = [
            'adapter'  => $adapter,
            'archive'  => $this->tmp . '/archive.zip',
            'fileName' => 'File.txt',
        ];
        $filter  = new CompressToArchive($options);
        $archive = $filter->filter('Some Text');
        self::assertFileExists($archive);

        $adapter->expandArchive($archive, $this->tmp);
        $expectFile = $this->tmp . '/File.txt';
        self::assertFileExists($expectFile);
        self::assertSame('Some Text', file_get_contents($expectFile));
    }

    /** @param Options $options */
    #[DataProvider('optionsProvider')]
    public function testYouCannotCompressArbitraryStringsWithoutConfiguringTheFileNameOption(array $options): void
    {
        $filter = new CompressToArchive($options);
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('The `fileName` option must be present when compressing arbitrary strings');

        $filter->filter('Whatever');
    }

    /** @return list<array{0: mixed}> */
    public static function unfilteredProvider(): array
    {
        return [
            [null],
            [123],
            [['foo']],
            [(object) ['foo' => 'bar']],
            [1.5],
            [true],
        ];
    }

    #[DataProvider('unfilteredProvider')]
    public function testUnfilteredArguments(mixed $value): void
    {
        $filter = new CompressToArchive([
            'archive' => $this->tmp . '/archive.tar',
        ]);

        self::assertSame($value, $filter->filter($value));
    }
}
