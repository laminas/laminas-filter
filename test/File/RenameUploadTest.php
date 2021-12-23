<?php

declare(strict_types=1);

namespace LaminasTest\Filter\File;

use Laminas\Filter\Exception;
use Laminas\Filter\File\RenameUpload as FileRenameUpload;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UploadedFileFactoryInterface;
use Psr\Http\Message\UploadedFileInterface;
use RuntimeException;
use stdClass;

use function array_shift;
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
    use ProphecyTrait;

    /**
     * Path to test files
     *
     * @var string
     */
    protected $filesPath;

    /**
     * Testfile
     *
     * @var string
     */
    protected $sourceFile;

    /**
     * Testfile
     *
     * @var string
     */
    protected $targetFile;

    /**
     * Testdirectory
     *
     * @var string
     */
    protected $targetPath;

    /**
     * Testfile in Testdirectory
     *
     * @var string
     */
    protected $targetPathFile;

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

    protected function removeDir($dir)
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
                continue;
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
        $this->assertSame($this->targetFile, $filter->getTarget());
        $this->assertSame('falsefile', $filter('falsefile'));
        $this->expectException(Exception\RuntimeException::class);
        $this->expectExceptionMessage('could not be renamed');
        $this->assertSame($this->targetFile, $filter($this->sourceFile));
    }

    public function testOptions(): void
    {
        $filter = new FileRenameUpload($this->targetFile);
        $this->assertSame($this->targetFile, $filter->getTarget());
        $this->assertFalse($filter->getUseUploadName());
        $this->assertFalse($filter->getOverwrite());
        $this->assertFalse($filter->getRandomize());

        $filter = new FileRenameUpload([
            'target'          => $this->sourceFile,
            'use_upload_name' => true,
            'overwrite'       => true,
            'randomize'       => true,
        ]);
        $this->assertSame($this->sourceFile, $filter->getTarget());
        $this->assertTrue($filter->getUseUploadName());
        $this->assertTrue($filter->getOverwrite());
        $this->assertTrue($filter->getRandomize());
    }

    public function testStringConstructorParam(): void
    {
        $filter = new RenameUploadMock($this->targetFile);
        $this->assertSame($this->targetFile, $filter->getTarget());
        $this->assertSame($this->targetFile, $filter($this->sourceFile));
        $this->assertSame('falsefile', $filter('falsefile'));
    }

    public function testStringConstructorWithFilesArray(): void
    {
        $filter = new RenameUploadMock($this->targetFile);
        $this->assertSame($this->targetFile, $filter->getTarget());
        $this->assertSame(
            [
                'tmp_name' => $this->targetFile,
                'name'     => $this->targetFile,
            ],
            $filter([
                'tmp_name' => $this->sourceFile,
                'name'     => $this->targetFile,
            ])
        );
        $this->assertSame('falsefile', $filter('falsefile'));
    }

    /**
     * @requires PHP 7
     */
    public function testStringConstructorWithPsrFile(): void
    {
        $sourceFile = $this->sourceFile;
        $targetFile = $this->targetFile;

        $originalStream = $this->prophesize(StreamInterface::class);
        $originalStream->getMetadata('uri')->willReturn($this->sourceFile);

        $originalFile = $this->prophesize(UploadedFileInterface::class);
        $originalFile->getStream()->will(function ($args, $mock) use ($originalStream) {
            $mock->getStream()->willThrow(new RuntimeException('Cannot call getStream() more than once'));

            return $originalStream->reveal();
        });
        $originalFile->getClientFilename()->willReturn($targetFile);
        $originalFile
            ->moveTo($targetFile)
            ->will(function ($args) use ($sourceFile) {
                $targetFile = array_shift($args);
                copy($sourceFile, $targetFile);
            })
            ->shouldBeCalled();
        $originalFile->getClientMediaType()->willReturn(null);

        $renamedStream = $this->prophesize(StreamInterface::class);
        $streamFactory = $this->prophesize(StreamFactoryInterface::class);
        $streamFactory
            ->createStreamFromFile($targetFile)
            ->will([$renamedStream, 'reveal']);

        $renamedFile = $this->prophesize(UploadedFileInterface::class);

        $fileFactory = $this->prophesize(UploadedFileFactoryInterface::class);
        $fileFactory
            ->createUploadedFile(
                Argument::that([$renamedStream, 'reveal']),
                0, // we can hardcode this, as we know the file is empty
                UPLOAD_ERR_OK,
                $targetFile,
                null
            )
            ->will([$renamedFile, 'reveal']);

        $filter = new RenameUploadMock($targetFile);
        $this->assertSame($targetFile, $filter->getTarget());

        $filter->setStreamFactory($streamFactory->reveal());
        $filter->setUploadFileFactory($fileFactory->reveal());

        $moved = $filter($originalFile->reveal());

        $this->assertSame($renamedFile->reveal(), $moved);

        $secondResult = $filter($originalFile->reveal());

        $this->assertSame($moved, $secondResult);
    }

    public function testArrayConstructorParam(): void
    {
        $filter = new RenameUploadMock([
            'target' => $this->targetFile,
        ]);
        $this->assertSame($this->targetFile, $filter->getTarget());
        $this->assertSame($this->targetFile, $filter($this->sourceFile));
        $this->assertSame('falsefile', $filter('falsefile'));
    }

    public function testConstructTruncatedTarget(): void
    {
        $filter = new FileRenameUpload('*');
        $this->assertSame('*', $filter->getTarget());
        $this->assertSame($this->sourceFile, $filter($this->sourceFile));
        $this->assertSame('falsefile', $filter('falsefile'));
    }

    public function testTargetDirectory(): void
    {
        $filter = new RenameUploadMock($this->targetPath);
        $this->assertSame($this->targetPath, $filter->getTarget());
        $this->assertSame($this->targetPathFile, $filter($this->sourceFile));
        $this->assertSame('falsefile', $filter('falsefile'));
    }

    public function testOverwriteWithExistingFile(): void
    {
        $filter = new RenameUploadMock([
            'target'    => $this->targetFile,
            'overwrite' => true,
        ]);

        copy($this->sourceFile, $this->targetFile);

        $this->assertSame($this->targetFile, $filter->getTarget());
        $this->assertSame($this->targetFile, $filter($this->sourceFile));
    }

    public function testCannotOverwriteExistingFile(): void
    {
        $filter = new RenameUploadMock([
            'target'    => $this->targetFile,
            'overwrite' => false,
        ]);

        copy($this->sourceFile, $this->targetFile);

        $this->assertSame($this->targetFile, $filter->getTarget());
        $this->assertFalse($filter->getOverwrite());
        $this->expectException(Exception\InvalidArgumentException::class);
        $this->expectExceptionMessage('already exists');
        $this->assertSame($this->targetFile, $filter($this->sourceFile));
    }

    public function testGetRandomizedFile(): void
    {
        $fileNoExt = $this->filesPath . DIRECTORY_SEPARATOR . 'newfile';
        $filter    = new RenameUploadMock([
            'target'    => $this->targetFile,
            'randomize' => true,
        ]);

        $this->assertMatchesRegularExpression(
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

        $this->assertMatchesRegularExpression(
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

        $this->assertMatchesRegularExpression(
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

        $this->assertMatchesRegularExpression(
            '#' . str_replace('\\', '\\\\', $fileNoExt) . '_.{13}#',
            $filter($this->sourceFile)
        );
    }

    public function testInvalidConstruction(): void
    {
        $this->expectException(Exception\InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid target');
        $filter = new FileRenameUpload(1234);
    }

    public function testCanFilterMultipleTimesWithSameResult(): void
    {
        $filter = new RenameUploadMock([
            'target'    => $this->targetFile,
            'randomize' => true,
        ]);

        $firstResult = $filter($this->sourceFile);

        $this->assertStringContainsString('newfile', $firstResult);

        $secondResult = $filter($this->sourceFile);

        $this->assertSame($firstResult, $secondResult);
    }

    public function returnUnfilteredDataProvider()
    {
        return [
            [null],
            [new stdClass()],
            [
                [
                    $this->sourceFile,
                    'something invalid',
                ],
            ],
        ];
    }

    /**
     * @dataProvider returnUnfilteredDataProvider
     */
    public function testReturnUnfiltered($input): void
    {
        $filter = new RenameUploadMock([
            'target'    => $this->targetFile,
            'randomize' => true,
        ]);

        $this->assertSame($input, $filter($input));
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
        $this->assertSame($sapiTarget, $filter($sapiSource));
        $this->assertSame($sapiTarget, $filter($sapiSource));
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

        $this->assertSame($source, $filter($source));
    }
}
