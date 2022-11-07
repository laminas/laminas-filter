<?php

declare(strict_types=1);

namespace LaminasTest\Filter\File;

use Laminas\Filter\Exception;
use Laminas\Filter\File\RenameUpload as FileRenameUpload;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UploadedFileFactoryInterface;
use Psr\Http\Message\UploadedFileInterface;
use stdClass;

use function basename;
use function copy;
use function glob;
use function is_dir;
use function is_file;
use function mkdir;
use function pathinfo;
use function rmdir;
use function sprintf;
use function str_replace;
use function sys_get_temp_dir;
use function touch;
use function uniqid;
use function unlink;

use const DIRECTORY_SEPARATOR;
use const UPLOAD_ERR_OK;

class RenameUploadTest extends TestCase
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

        foreach (glob($dir . DIRECTORY_SEPARATOR . '*') as $file) {
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
        $filter = new FileRenameUpload($this->targetFile);
        self::assertSame($this->targetFile, $filter->getTarget());
        self::assertSame('falsefile', $filter('falsefile'));
        $this->expectException(Exception\RuntimeException::class);
        $this->expectExceptionMessage('could not be renamed');
        self::assertSame($this->targetFile, $filter($this->sourceFile));
    }

    public function testOptions(): void
    {
        $filter = new FileRenameUpload($this->targetFile);
        self::assertSame($this->targetFile, $filter->getTarget());
        self::assertFalse($filter->getUseUploadName());
        self::assertFalse($filter->getOverwrite());
        self::assertFalse($filter->getRandomize());

        $filter = new FileRenameUpload([
            'target'          => $this->sourceFile,
            'use_upload_name' => true,
            'overwrite'       => true,
            'randomize'       => true,
        ]);
        self::assertSame($this->sourceFile, $filter->getTarget());
        self::assertTrue($filter->getUseUploadName());
        self::assertTrue($filter->getOverwrite());
        self::assertTrue($filter->getRandomize());
    }

    public function testStringConstructorParam(): void
    {
        $filter = new RenameUploadMock($this->targetFile);
        self::assertSame($this->targetFile, $filter->getTarget());
        self::assertSame($this->targetFile, $filter($this->sourceFile));
        self::assertSame('falsefile', $filter('falsefile'));
    }

    public function testStringConstructorWithFilesArray(): void
    {
        $filter = new RenameUploadMock($this->targetFile);
        self::assertSame($this->targetFile, $filter->getTarget());
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

    /**
     * @requires PHP 7
     */
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

        $filter = new RenameUploadMock($this->targetFile);
        self::assertSame($this->targetFile, $filter->getTarget());

        $filter->setStreamFactory($streamFactory);
        $filter->setUploadFileFactory($fileFactory);

        $moved = $filter($originalFile);

        self::assertSame($renamedFile, $moved);

        $secondResult = $filter($originalFile);

        self::assertSame($moved, $secondResult);
    }

    public function testArrayConstructorParam(): void
    {
        $filter = new RenameUploadMock([
            'target' => $this->targetFile,
        ]);
        self::assertSame($this->targetFile, $filter->getTarget());
        self::assertSame($this->targetFile, $filter($this->sourceFile));
        self::assertSame('falsefile', $filter('falsefile'));
    }

    public function testConstructTruncatedTarget(): void
    {
        $filter = new FileRenameUpload('*');
        self::assertSame('*', $filter->getTarget());
        self::assertSame($this->sourceFile, $filter($this->sourceFile));
        self::assertSame('falsefile', $filter('falsefile'));
    }

    public function testTargetDirectory(): void
    {
        $filter = new RenameUploadMock($this->targetPath);
        self::assertSame($this->targetPath, $filter->getTarget());
        self::assertSame($this->targetPathFile, $filter($this->sourceFile));
        self::assertSame('falsefile', $filter('falsefile'));
    }

    public function testOverwriteWithExistingFile(): void
    {
        $filter = new RenameUploadMock([
            'target'    => $this->targetFile,
            'overwrite' => true,
        ]);

        copy($this->sourceFile, $this->targetFile);

        self::assertSame($this->targetFile, $filter->getTarget());
        self::assertSame($this->targetFile, $filter($this->sourceFile));
    }

    public function testCannotOverwriteExistingFile(): void
    {
        $filter = new RenameUploadMock([
            'target'    => $this->targetFile,
            'overwrite' => false,
        ]);

        copy($this->sourceFile, $this->targetFile);

        self::assertSame($this->targetFile, $filter->getTarget());
        self::assertFalse($filter->getOverwrite());
        $this->expectException(Exception\InvalidArgumentException::class);
        $this->expectExceptionMessage('already exists');
        self::assertSame($this->targetFile, $filter($this->sourceFile));
    }

    public function testGetRandomizedFile(): void
    {
        $fileNoExt = $this->filesPath . DIRECTORY_SEPARATOR . 'newfile';
        $filter    = new RenameUploadMock([
            'target'    => $this->targetFile,
            'randomize' => true,
        ]);

        self::assertMatchesRegularExpression(
            '#' . str_replace('\\', '\\\\', $fileNoExt) . '_.{23}\.xml#',
            $filter($this->sourceFile)
        );
    }

    public function testGetFileWithOriginalExtension(): void
    {
        $fileNoExt = $this->filesPath . DIRECTORY_SEPARATOR . 'newfile';
        $filter    = new RenameUploadMock([
            'target'               => $this->targetFile,
            'use_upload_extension' => true,
            'randomize'            => false,
        ]);

        $oldFilePathInfo = pathinfo($this->sourceFile);

        self::assertMatchesRegularExpression(
            '#' . str_replace('\\', '\\\\', $fileNoExt) . '.' . $oldFilePathInfo['extension'] . '#',
            $filter($this->sourceFile)
        );
    }

    public function testGetRandomizedFileWithOriginalExtension(): void
    {
        $fileNoExt = $this->filesPath . DIRECTORY_SEPARATOR . 'newfile';
        $filter    = new RenameUploadMock([
            'target'               => $this->targetFile,
            'use_upload_extension' => true,
            'randomize'            => true,
        ]);

        $oldFilePathInfo = pathinfo($this->sourceFile);

        self::assertMatchesRegularExpression(
            '#' . str_replace('\\', '\\\\', $fileNoExt) . '_.{23}\.' . $oldFilePathInfo['extension'] . '#',
            $filter($this->sourceFile)
        );
    }

    public function testGetRandomizedFileWithoutExtension(): void
    {
        $fileNoExt = $this->filesPath . DIRECTORY_SEPARATOR . 'newfile';
        $filter    = new RenameUploadMock([
            'target'    => $fileNoExt,
            'randomize' => true,
        ]);

        self::assertMatchesRegularExpression(
            '#' . str_replace('\\', '\\\\', $fileNoExt) . '_.{13}#',
            $filter($this->sourceFile)
        );
    }

    public function testInvalidConstruction(): void
    {
        $this->expectException(Exception\InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid target');
        new FileRenameUpload(1234);
    }

    public function testCanFilterMultipleTimesWithSameResult(): void
    {
        $filter = new RenameUploadMock([
            'target'    => $this->targetFile,
            'randomize' => true,
        ]);

        $firstResult = $filter($this->sourceFile);

        self::assertStringContainsString('newfile', $firstResult);

        $secondResult = $filter($this->sourceFile);

        self::assertSame($firstResult, $secondResult);
    }

    /** @return list<array{0:mixed|null}> */
    public function returnUnfilteredDataProvider(): array
    {
        return [
            [null],
            [new stdClass()],
            [
                [
                    '/some-file',
                    'something invalid',
                ],
            ],
        ];
    }

    /**
     * @dataProvider returnUnfilteredDataProvider
     */
    public function testReturnUnfiltered(mixed $input): void
    {
        $filter = new RenameUploadMock([
            'target'    => $this->targetFile,
            'randomize' => true,
        ]);

        self::assertSame($input, $filter($input));
    }

    /**
     * @see https://github.com/zendframework/zend-filter/issues/77
     */
    public function testFilterDoesNotAlterUnknownFileDataAndCachesResultsOfFilteringSAPIUploads(): void
    {
        $filter = new RenameUploadMock($this->targetPath);

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
        $filter = new RenameUploadMock();

        $source = [
            'tmp_name' => $this->sourceFile,
            'name'     => basename($this->targetFile),
        ];

        self::assertSame($source, $filter($source));
    }
}
