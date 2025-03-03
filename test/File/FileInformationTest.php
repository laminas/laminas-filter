<?php

declare(strict_types=1);

namespace LaminasTest\Filter\File;

use Laminas\Diactoros\UploadedFile;
use Laminas\Filter\File\FileInformation;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

use function chmod;
use function filesize;
use function touch;
use function unlink;

use const UPLOAD_ERR_OK;

/** @psalm-suppress InternalClass, InternalMethod, InternalProperty */
#[CoversClass(FileInformation::class)]
final class FileInformationTest extends TestCase
{
    public function testThatANonExistentFileCannotBeCreatedFromAString(): void
    {
        $this->expectExceptionMessage('Cannot detect any file information');
        FileInformation::factory('Foo');
    }

    public function testThatANonExistentFileCannotBeCreatedFromASapiFileArray(): void
    {
        $this->expectExceptionMessage('Cannot detect any file information');
        FileInformation::factory([
            'error'    => UPLOAD_ERR_OK,
            'tmp_name' => 'Foo',
            'name'     => 'Foo',
            'type'     => 'text/plain',
            'size'     => 0,
        ]);
    }

    public function testExpectedValuesForFilePath(): void
    {
        $path = __DIR__ . '/fixtures/File1.txt';
        $file = FileInformation::factory($path);

        self::assertSame($path, $file->path);
        self::assertNull($file->clientFileName);
        self::assertNull($file->clientMediaType);
        self::assertTrue($file->readable);
        self::assertSame('File1.txt', $file->baseName);
        self::assertSame('text/plain', $file->detectMimeType());
    }

    public function testExpectedValuesForUploadedFile(): void
    {
        $path = __DIR__ . '/fixtures/File1.txt';
        $size = filesize($path);
        self::assertIsInt($size);

        $upload = new UploadedFile(
            $path,
            $size,
            UPLOAD_ERR_OK,
            'Foo.txt',
            'text/plain',
        );

        $file = FileInformation::factory($upload);

        self::assertSame($path, $file->path);
        self::assertSame('Foo.txt', $file->clientFileName);
        self::assertSame('text/plain', $file->clientMediaType);
        self::assertTrue($file->readable);
        self::assertSame('File1.txt', $file->baseName);
        self::assertSame('text/plain', $file->detectMimeType());
    }

    public function testExpectedValuesForSapiFilesArray(): void
    {
        $path = __DIR__ . '/fixtures/File1.txt';

        $upload = [
            'tmp_name' => $path,
            'size'     => filesize($path),
            'error'    => UPLOAD_ERR_OK,
            'name'     => 'Foo.txt',
            'type'     => 'text/plain',
        ];

        $file = FileInformation::factory($upload);

        self::assertSame($path, $file->path);
        self::assertSame('Foo.txt', $file->clientFileName);
        self::assertSame('text/plain', $file->clientMediaType);
        self::assertTrue($file->readable);
        self::assertSame('File1.txt', $file->baseName);
        self::assertSame('text/plain', $file->detectMimeType());
    }

    public function testUnReadableFile(): void
    {
        $path = __DIR__ . '/fixtures/no-read.txt';
        touch($path);
        chmod($path, 0333);
        try {
            $file = FileInformation::factory($path);
            self::assertFalse($file->readable);
        } finally {
            unlink($path);
        }
    }
}
