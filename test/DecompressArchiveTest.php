<?php

declare(strict_types=1);

namespace LaminasTest\Filter;

use Laminas\Diactoros\UploadedFile;
use Laminas\Filter\DecompressArchive;
use Laminas\Filter\Exception\InvalidArgumentException;
use LaminasTest\Filter\Compress\TmpDirectory;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

use function filesize;
use function mkdir;
use function sys_get_temp_dir;
use function uniqid;

use const DIRECTORY_SEPARATOR;
use const UPLOAD_ERR_OK;

class DecompressArchiveTest extends TestCase
{
    /** @var non-empty-string */
    private string $target;

    protected function setUp(): void
    {
        $this->target = sys_get_temp_dir() . '/laminas-filter-' . uniqid();
        mkdir($this->target);
    }

    protected function tearDown(): void
    {
        TmpDirectory::cleanUp($this->target);
    }

    public static function archiveProvider(): array
    {
        return [
            [__DIR__ . '/Compress/fixtures/File1.tar', 'File1.txt'],
            [__DIR__ . '/Compress/fixtures/File1.tar.gz', 'File1.txt'],
            [__DIR__ . '/Compress/fixtures/File1.tar.bz2', 'File1.txt'],
            [__DIR__ . '/Compress/fixtures/File1.zip', 'File1.txt'],
            [__DIR__ . '/Compress/fixtures/TarArchiveWithNoExtension', 'File1.txt'],
            [__DIR__ . '/Compress/fixtures/ZipArchiveWithNoExtension', 'File1.txt'],
        ];
    }

    #[DataProvider('archiveProvider')]
    public function testThatRegularFilePathsWillBeDecompressed(string $value, string $expectFile): void
    {
        $filter = new DecompressArchive(['target' => $this->target]);

        $target = $filter->filter($value);
        self::assertSame($this->target, $target);

        self::assertFileExists($target . DIRECTORY_SEPARATOR . $expectFile);
    }

    #[DataProvider('archiveProvider')]
    public function testInvoke(string $value, string $expectFile): void
    {
        $filter = new DecompressArchive(['target' => $this->target]);

        $target = $filter->__invoke($value);
        self::assertSame($this->target, $target);

        self::assertFileExists($target . DIRECTORY_SEPARATOR . $expectFile);
    }

    #[DataProvider('archiveProvider')]
    public function testThatPHPFileArraysWillBeDecompressed(string $value, string $expectFile): void
    {
        $filter = new DecompressArchive(['target' => $this->target]);

        $data = [
            'error'    => UPLOAD_ERR_OK,
            'tmp_name' => $value,
            'name'     => 'Foo',
            'type'     => 'text/plain',
            'size'     => 0,
        ];

        $target = $filter->filter($data);
        self::assertSame($this->target, $target);

        self::assertFileExists($target . DIRECTORY_SEPARATOR . $expectFile);
    }

    #[DataProvider('archiveProvider')]
    public function testThatPsr7UploadsWillBeDecompressed(string $value, string $expectFile): void
    {
        $filter = new DecompressArchive(['target' => $this->target]);

        $size = filesize($value);
        self::assertIsInt($size);

        $upload = new UploadedFile(
            $value,
            $size,
            UPLOAD_ERR_OK,
            'Foo.txt',
            'text/plain',
        );

        $target = $filter->filter($upload);
        self::assertSame($this->target, $target);

        self::assertFileExists($target . DIRECTORY_SEPARATOR . $expectFile);
    }

    /** @return list<array{0: mixed}> */
    public static function unfilteredValues(): array
    {
        return [
            [null],
            ['foo'],
            [['foo']],
            [1],
            ['/not-there'],
            ['/not-there.zip'],
            [__FILE__],
        ];
    }

    #[DataProvider('unfilteredValues')]
    public function testUnFilterableValues(mixed $value): void
    {
        $filter = new DecompressArchive(['target' => $this->target]);

        self::assertSame($value, $filter->filter($value));
    }

    public function testInvalidTargetIsExceptional(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new DecompressArchive(['target' => '/not-there']);
    }
}
