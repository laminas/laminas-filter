<?php

declare(strict_types=1);

namespace LaminasTest\Filter\File;

use Laminas\Filter\Exception;
use Laminas\Filter\File\RenameUpload as FileRenameUpload;
use Laminas\Filter\File\UploadedFileMoverInterface;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UploadedFileFactoryInterface;
use Psr\Http\Message\UploadedFileInterface;
use stdClass;

use function assert;
use function basename;
use function copy;
use function glob;
use function is_dir;
use function is_file;
use function mkdir;
use function pathinfo;
use function rename;
use function rmdir;
use function sprintf;
use function str_replace;
use function sys_get_temp_dir;
use function touch;
use function uniqid;
use function unlink;

use const DIRECTORY_SEPARATOR;
use const UPLOAD_ERR_OK;

final class RenameUploadTest extends TestCase
{
    /**
     * Path to test files
     */
    private string $filesPath;

    /**
     * Test file
     */
    private string $sourceFile;

    /**
     * Test file
     */
    private string $targetFile;

    /**
     * Test directory
     */
    private string $targetPath;

    /**
     * Test file in Test directory
     */
    private string $targetPathFile;

    /**
     * Sets the path to test files
     */
    public function setUp(): void
    {
        $this->filesPath  = sprintf('%s%s%s', sys_get_temp_dir(), DIRECTORY_SEPARATOR, uniqid('laminasilter'));
        $this->targetPath = sprintf('%s%s%s', $this->filesPath, DIRECTORY_SEPARATOR, 'targetPath');

        mkdir($this->targetPath, 0775, true);

        $this->sourceFile     = $this->filesPath . DIRECTORY_SEPARATOR . 'testfile.txt';
        $this->targetFile     = $this->filesPath . DIRECTORY_SEPARATOR . 'newfile.xml';
        $this->targetPathFile = $this->targetPath . DIRECTORY_SEPARATOR . 'testfile.txt';

        touch($this->sourceFile);
    }

    /**
     * Sets the path to test files
     */
    public function tearDown(): void
    {
        $this->removeDir($this->filesPath);
    }

    private function removeDir(string $dir): void
    {
        if (! is_dir($dir)) {
            return;
        }

        $list = glob($dir . DIRECTORY_SEPARATOR . '*');
        self::assertIsArray($list);
        foreach ($list as $file) {
            if (is_file($file)) {
                unlink($file);
                continue;
            }
            if (is_dir($file)) {
                $this->removeDir($file);
            }
        }

        rmdir($dir);
    }

    /**
     * Test single parameter filter
     */
    public function testThrowsExceptionWithNonUploadedFile(): void
    {
        $filter = new FileRenameUpload(['target_directory' => $this->targetFile]);
        self::assertSame('falsefile', $filter('falsefile'));

        $this->expectException(Exception\RuntimeException::class);
        $this->expectExceptionMessage('could not be renamed');
        self::assertSame($this->targetFile, $filter($this->sourceFile));
    }

    public function testStringConstructorWithFilesArray(): void
    {
        $filter = new FileRenameUpload([
            'target_directory'  => $this->targetFile,
            'upload_file_mover' => $this->createRenameMover(),
        ]);

        self::assertSame(
            [
                'tmp_name' => $this->targetFile,
                'name'     => $this->targetFile,
            ],
            $filter([
                'tmp_name' => $this->sourceFile,
                'name'     => $this->targetFile,
            ])
        );
        self::assertSame('falsefile', $filter('falsefile'));
    }

    public function testStringConstructorWithPsrFile(): void
    {
        $originalStream = $this->createMock(StreamInterface::class);
        $originalStream->expects(self::once())
            ->method('getMetadata')
            ->with('uri')
            ->willReturn($this->sourceFile);

        $originalFile = $this->createMock(UploadedFileInterface::class);
        $originalFile->expects(self::once())
            ->method('getStream')
            ->willReturn($originalStream);

        $originalFile->expects(self::atLeast(1))
            ->method('getClientFilename')
            ->willReturn($this->targetFile);

        $originalFile->expects(self::once())
            ->method('moveTo')
            ->with(self::callback(function ($argument): bool {
                self::assertSame($this->targetFile, $argument);
                copy($this->sourceFile, $this->targetFile);

                return true;
            }));

        $originalFile->expects(self::once())
            ->method('getClientMediaType')
            ->willReturn(null);

        $renamedStream = $this->createMock(StreamInterface::class);
        $streamFactory = $this->createMock(StreamFactoryInterface::class);
        $streamFactory->expects(self::once())
            ->method('createStreamFromFile')
            ->with($this->targetFile)
            ->willReturn($renamedStream);

        $renamedFile = $this->createMock(UploadedFileInterface::class);

        $fileFactory = $this->createMock(UploadedFileFactoryInterface::class);
        $fileFactory->expects(self::once())
            ->method('createUploadedFile')
            ->with(
                $renamedStream,
                0, // we can hardcode this, as we know the file is empty
                UPLOAD_ERR_OK,
                $this->targetFile,
                null
            )
            ->willReturn($renamedFile);

        $filter = new FileRenameUpload([
            'target_directory'    => $this->targetFile,
            'stream_factory'      => $streamFactory,
            'upload_file_factory' => $fileFactory,
            'upload_file_mover'   => $this->createRenameMover(),
        ]);

        $moved = $filter($originalFile);

        self::assertSame($renamedFile, $moved);

        $secondResult = $filter($originalFile);

        self::assertSame($moved, $secondResult);
    }

    public function testWithPsrFileWillFailWithMissingUri(): void
    {
        $originalFile = $this->createMock(UploadedFileInterface::class);
        $originalFile->method('getStream')->willReturn(
            $this->createMock(StreamInterface::class)
        );
        $filter = new FileRenameUpload([
            'upload_file_mover' => $this->createRenameMover(),
        ]);
        self::expectException(Exception\RuntimeException::class);
        self::expectExceptionMessage('UploadedFile doesn\'t contains the uri metadata');
        $filter($originalFile);
    }

    public function testWithPsrFileWillFailWithMissingStreamFactory(): void
    {
        $originalStream = $this->createMock(StreamInterface::class);
        $originalStream->expects(self::once())
            ->method('getMetadata')
            ->with('uri')
            ->willReturn($this->sourceFile);

        $originalFile = $this->createMock(UploadedFileInterface::class);
        $originalFile->expects(self::once())
            ->method('getStream')
            ->willReturn($originalStream);

        $originalFile->expects(self::atLeast(1))
            ->method('getClientFilename')
            ->willReturn($this->targetFile);

        $filter = new FileRenameUpload([
            'target_directory'  => $this->targetFile,
            'upload_file_mover' => $this->createRenameMover(),
        ]);
        self::expectException(Exception\RuntimeException::class);
        self::expectExceptionMessage('pass the stream_factory option');
        $filter($originalFile);
    }

    public function testWithPsrFileWillFailWithMissingUploadedFileFactory(): void
    {
        $originalStream = $this->createMock(StreamInterface::class);
        $originalStream->expects(self::once())
            ->method('getMetadata')
            ->with('uri')
            ->willReturn($this->sourceFile);

        $originalFile = $this->createMock(UploadedFileInterface::class);
        $originalFile->expects(self::once())
            ->method('getStream')
            ->willReturn($originalStream);

        $originalFile->expects(self::atLeast(1))
            ->method('getClientFilename')
            ->willReturn($this->targetFile);

        $streamFactory = $this->createMock(StreamFactoryInterface::class);
        $streamFactory->expects(self::once())
            ->method('createStreamFromFile')
            ->with($this->targetFile)
            ->willReturn($this->createMock(StreamInterface::class));

        $filter = new FileRenameUpload([
            'target_directory'  => $this->targetFile,
            'stream_factory'    => $streamFactory,
            'upload_file_mover' => $this->createRenameMover(),
        ]);
        self::expectException(Exception\RuntimeException::class);
        self::expectExceptionMessage('pass the upload_file_factory option');
        $filter($originalFile);
    }

    public function testArrayConstructorParam(): void
    {
        $filter = new FileRenameUpload([
            'target_directory'  => $this->targetFile,
            'upload_file_mover' => $this->createRenameMover(),
        ]);
        self::assertSame($this->targetFile, $filter($this->sourceFile));
        self::assertSame($this->targetFile, $filter->filter($this->sourceFile));
        self::assertSame($this->targetFile, $filter->__invoke($this->sourceFile));
        self::assertSame('falsefile', $filter('falsefile'));
    }

    public function testConstructTruncatedTarget(): void
    {
        $filter = new FileRenameUpload([
            'target_directory'  => '*',
            'upload_file_mover' => $this->createRenameMover(),
        ]);
        self::assertSame($this->sourceFile, $filter($this->sourceFile));
        self::assertSame('falsefile', $filter('falsefile'));
    }

    public function testOverwriteWithExistingFile(): void
    {
        $filter = new FileRenameUpload([
            'target_directory'  => $this->targetFile,
            'overwrite'         => true,
            'upload_file_mover' => $this->createRenameMover(),
        ]);

        copy($this->sourceFile, $this->targetFile);

        self::assertSame($this->targetFile, $filter($this->sourceFile));
    }

    public function testCannotOverwriteExistingFile(): void
    {
        $filter = new FileRenameUpload([
            'target_directory'  => $this->targetFile,
            'overwrite'         => false,
            'upload_file_mover' => $this->createRenameMover(),
        ]);

        copy($this->sourceFile, $this->targetFile);

        $this->expectException(Exception\InvalidArgumentException::class);
        $this->expectExceptionMessage('already exists');
        self::assertSame($this->targetFile, $filter($this->sourceFile));
    }

    public function testGetRandomizedFile(): void
    {
        $fileNoExt = $this->filesPath . DIRECTORY_SEPARATOR . 'newfile';
        $filter    = new FileRenameUpload([
            'target_directory'  => $this->targetFile,
            'randomize'         => true,
            'upload_file_mover' => $this->createRenameMover(),
        ]);

        self::assertMatchesRegularExpression(
            '#' . str_replace('\\', '\\\\', $fileNoExt) . '_.{23}\.xml#',
            $filter($this->sourceFile)
        );
    }

    public function testGetFileWithOriginalExtension(): void
    {
        $fileNoExt = $this->filesPath . DIRECTORY_SEPARATOR . 'newfile';
        $filter    = new FileRenameUpload([
            'target_directory'     => $this->targetFile,
            'use_upload_extension' => true,
            'randomize'            => false,
            'upload_file_mover'    => $this->createRenameMover(),
        ]);

        $oldFilePathInfo = pathinfo($this->sourceFile);
        assert(isset($oldFilePathInfo['extension']));

        self::assertMatchesRegularExpression(
            '#' . str_replace('\\', '\\\\', $fileNoExt) . '.' . $oldFilePathInfo['extension'] . '#',
            $filter($this->sourceFile)
        );
    }

    public function testGetRandomizedFileWithOriginalExtension(): void
    {
        $fileNoExt = $this->filesPath . DIRECTORY_SEPARATOR . 'newfile';
        $filter    = new FileRenameUpload([
            'target_directory'     => $this->targetFile,
            'use_upload_extension' => true,
            'randomize'            => true,
            'upload_file_mover'    => $this->createRenameMover(),
        ]);

        $oldFilePathInfo = pathinfo($this->sourceFile);
        assert(isset($oldFilePathInfo['extension']));

        self::assertMatchesRegularExpression(
            '#' . str_replace('\\', '\\\\', $fileNoExt) . '_.{23}\.' . $oldFilePathInfo['extension'] . '#',
            $filter($this->sourceFile)
        );
    }

    public function testGetRandomizedFileWithoutExtension(): void
    {
        $fileNoExt = $this->filesPath . DIRECTORY_SEPARATOR . 'newfile';
        $filter    = new FileRenameUpload([
            'target_directory'  => $fileNoExt,
            'randomize'         => true,
            'upload_file_mover' => $this->createRenameMover(),
        ]);

        self::assertMatchesRegularExpression(
            '#' . str_replace('\\', '\\\\', $fileNoExt) . '_.{13}#',
            $filter($this->sourceFile)
        );
    }

    public function testCanFilterMultipleTimesWithSameResult(): void
    {
        $filter = new FileRenameUpload([
            'target_directory'  => $this->targetFile,
            'randomize'         => true,
            'upload_file_mover' => $this->createRenameMover(),
        ]);

        $firstResult = $filter($this->sourceFile);

        self::assertStringContainsString('newfile', $firstResult);

        $secondResult = $filter($this->sourceFile);

        self::assertSame($firstResult, $secondResult);
    }

    /** @return list<array{0:mixed|null}> */
    public static function returnUnfilteredDataProvider(): array
    {
        return [
            [null],
            [new stdClass()],
            [false],
            [
                [
                    '/some-file',
                    'something invalid',
                ],
            ],
        ];
    }

    #[DataProvider('returnUnfilteredDataProvider')]
    public function testReturnUnfiltered(mixed $input): void
    {
        $filter = new FileRenameUpload([
            'target_directory'  => $this->targetFile,
            'randomize'         => true,
            'upload_file_mover' => $this->createRenameMover(),
        ]);

        self::assertSame($input, $filter($input));
    }

    /**
     * @see https://github.com/zendframework/zend-filter/issues/77
     */
    public function testFilterDoesNotAlterUnknownFileDataAndCachesResultsOfFilteringSAPIUploads(): void
    {
        $filter = new FileRenameUpload([
            'target_directory'  => $this->targetPath,
            'upload_file_mover' => $this->createRenameMover(),
        ]);

        // Emulate the output of \Laminas\Http\Request::getFiles()->toArray()
        $sapiSource = [
            'tmp_name' => $this->sourceFile,
            'name'     => basename($this->targetFile),
            'type'     => 'text/plain',
            'error'    => UPLOAD_ERR_OK,
            'size'     => 123,
        ];

        $sapiTarget = [
            'tmp_name' => $this->targetPathFile,
            'name'     => basename($this->targetFile),
            'type'     => 'text/plain',
            'error'    => UPLOAD_ERR_OK,
            'size'     => 123,
        ];

        // Check the result twice for the `alreadyFiltered` cache path
        self::assertSame($sapiTarget, $filter($sapiSource));
        self::assertSame($sapiTarget, $filter($sapiSource));
    }

    /**
     * @see https://github.com/zendframework/zend-filter/issues/76
     */
    public function testFilterReturnsFileDataVerbatimUnderSAPIWhenTargetPathIsUnspecified(): void
    {
        $filter = new FileRenameUpload([
            'upload_file_mover' => $this->createRenameMover(),
        ]);

        $source = [
            'tmp_name' => $this->sourceFile,
            'name'     => basename($this->targetFile),
        ];

        self::assertSame($source, $filter($source));
    }

    private function createRenameMover(): UploadedFileMoverInterface
    {
        return new class implements UploadedFileMoverInterface {
            public function moveUploadedFile(string $sourceFile, string $targetFile): bool
            {
                rename($sourceFile, $targetFile);
                return true;
            }
        };
    }
}
