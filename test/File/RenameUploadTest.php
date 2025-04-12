<?php

declare(strict_types=1);

namespace LaminasTest\Filter\File;

use Laminas\Diactoros\UploadedFile;
use Laminas\Filter\Exception\InvalidArgumentException;
use Laminas\Filter\Exception\RuntimeException;
use Laminas\Filter\File\FileInformation;
use Laminas\Filter\File\MoveUploadedFile;
use Laminas\Filter\File\RenameUpload;
use Laminas\Filter\FilterPluginManager;
use LaminasTest\Filter\Compress\TmpDirectory;
use LaminasTest\Filter\TestAsset\InMemoryContainer;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

use function basename;
use function file_get_contents;
use function file_put_contents;
use function is_dir;
use function mkdir;
use function sprintf;
use function sys_get_temp_dir;
use function touch;
use function uniqid;
use function unlink;

use const UPLOAD_ERR_OK;

final class RenameUploadTest extends TestCase
{
    private static string|null $workDirectory = null;

    public static function workDirectory(): string
    {
        if (self::$workDirectory === null) {
            self::$workDirectory = sys_get_temp_dir() . '/' . uniqid('laminas_');
            mkdir(self::$workDirectory);
        }

        return self::$workDirectory;
    }

    public static function tearDownAfterClass(): void
    {
        if (self::$workDirectory === null) {
            return;
        }

        TmpDirectory::cleanUp(self::$workDirectory);
    }

    /** @return array<string, array{0: mixed}> */
    public static function unfilterableInput(): array
    {
        return [
            'Non-existent file path' => [__DIR__ . '/not-there'],
            'Non-existent SAPI path' => [
                [
                    'tmp_name' => __DIR__ . '/not-there',
                    'name'     => 'Whatever',
                    'size'     => 123,
                    'type'     => 'text/plain',
                    'error'    => 0,
                ],
            ],
            'A regular string'       => ['Fairies'],
            'Numeric'                => [123],
            'Array'                  => [['kermit', 'miss piggy']],
        ];
    }

    #[DataProvider('unfilterableInput')]
    public function testUnfilterableValuesAreReturnedUnFiltered(mixed $input): void
    {
        $filter = new RenameUpload([], new MoveUploadedFile());

        self::assertSame($input, $filter->filter($input));
    }

    /** @return array<string, array{0: mixed}> */
    public static function fileProvider(): array
    {
        $psr    = self::workDirectory() . sprintf('/%s.txt', uniqid('psr_'));
        $string = self::workDirectory() . sprintf('/%s.txt', uniqid('str_'));
        $sapi   = self::workDirectory() . sprintf('/%s.txt', uniqid('sapi_'));
        file_put_contents($psr, '.');
        file_put_contents($sapi, '.');
        file_put_contents($string, '.');

        return [
            'PSR'         => [
                new UploadedFile(
                    $psr,
                    1,
                    UPLOAD_ERR_OK,
                    'myfile.txt',
                    'text/plain',
                ),
            ],
            'SAPI'        => [
                [
                    'tmp_name' => $sapi,
                    'name'     => 'myfile.txt',
                    'size'     => 1,
                    'type'     => 'text/plain',
                    'error'    => UPLOAD_ERR_OK,
                ],
            ],
            'String Path' => [$string],
        ];
    }

    #[DataProvider('fileProvider')]
    public function testBasicOperationMovingUploadedFilesToADirectory(mixed $input): void
    {
        $file = FileInformation::factory($input);
        self::assertFileExists($file->path);

        $target = self::workDirectory() . '/target';
        if (! is_dir($target)) {
            mkdir($target);
        }

        $expectFile = $target . '/' . $file->baseName;

        $filter = new RenameUpload([
            'target' => $target,
        ], new MoveUploadedFile());

        $result = $filter->__invoke($input);

        self::assertSame($expectFile, $result);

        self::assertFileExists($result);
        self::assertFileDoesNotExist($file->path);
    }

    #[DataProvider('fileProvider')]
    public function testBasicRenameUsingUploadedFileName(mixed $input): void
    {
        $file = FileInformation::factory($input);
        self::assertFileExists($file->path);

        $target = self::workDirectory() . '/target';
        if (! is_dir($target)) {
            mkdir($target);
        }

        if ($file->clientFileName !== null) {
            $expectFile = $target . '/' . $file->clientFileName;
        } else {
            $expectFile = $target . '/' . $file->baseName;
        }

        $filter = new RenameUpload([
            'target'          => $target,
            'use_upload_name' => true,
        ], new MoveUploadedFile());

        $result = $filter->__invoke($input);

        self::assertSame($expectFile, $result);

        self::assertFileExists($result);
        self::assertFileDoesNotExist($file->path);

        unlink($result);
    }

    #[DataProvider('fileProvider')]
    public function testRenameWillFailWithDefaultOptions(mixed $input): void
    {
        $file = FileInformation::factory($input);
        self::assertFileExists($file->path);

        $filter = new RenameUpload([], new MoveUploadedFile());

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('could not be renamed. It already exists.');
        $filter->__invoke($input);
    }

    #[DataProvider('fileProvider')]
    public function testRenameInPlaceWillFailWhenOverwriteIsTrue(mixed $input): void
    {
        $file = FileInformation::factory($input);
        self::assertFileExists($file->path);

        $filter = new RenameUpload([
            'overwrite' => true,
        ], new MoveUploadedFile());

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('could not be renamed. An error occurred while processing the file.');
        $filter->__invoke($input);
    }

    #[DataProvider('fileProvider')]
    public function testRenameInPlaceWillSucceedWhenRandomizeIsTrue(mixed $input): void
    {
        $file = FileInformation::factory($input);
        self::assertFileExists($file->path);

        $filter = new RenameUpload([
            'randomize' => true,
        ], new MoveUploadedFile());

        $result = $filter->__invoke($input);
        self::assertIsString($result);

        self::assertFileExists($result);
        self::assertFileDoesNotExist($file->path);
    }

    #[DataProvider('fileProvider')]
    public function testRenameWithExactTargetFile(mixed $input): void
    {
        $directory = self::workDirectory() . '/target';
        if (! is_dir($directory)) {
            mkdir($directory);
        }

        $target = $directory . '/some-file.ext';

        $file = FileInformation::factory($input);
        self::assertFileExists($file->path);

        $filter = new RenameUpload([
            'target' => $target,
        ], new MoveUploadedFile());

        $result = $filter->__invoke($input);
        self::assertIsString($result);
        self::assertSame($target, $result);

        self::assertFileExists($result);
        self::assertFileDoesNotExist($file->path);

        unlink($result);
    }

    #[DataProvider('fileProvider')]
    public function testRenameFailsWhenOverwriteIsFalseAndTargetAlreadyExists(mixed $input): void
    {
        $directory = self::workDirectory() . '/target';
        if (! is_dir($directory)) {
            mkdir($directory);
        }

        $target = $directory . '/some-file.ext';
        touch($target);

        $file = FileInformation::factory($input);
        self::assertFileExists($file->path);

        $filter = new RenameUpload([
            'target'    => $target,
            'overwrite' => false,
        ], new MoveUploadedFile());

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('could not be renamed. It already exists.');
        $filter->__invoke($input);
    }

    #[DataProvider('fileProvider')]
    public function testRenameSucceedsWhenOverwriteIsTrueAndTargetAlreadyExists(mixed $input): void
    {
        $directory = self::workDirectory() . '/target';
        if (! is_dir($directory)) {
            mkdir($directory);
        }

        $target = $directory . '/some-file.ext';
        touch($target);

        $file = FileInformation::factory($input);
        self::assertFileExists($file->path);
        $contents = file_get_contents($file->path);
        self::assertSame('.', $contents);

        $filter = new RenameUpload([
            'target'    => $target,
            'overwrite' => true,
        ], new MoveUploadedFile());

        $result = $filter->__invoke($input);

        self::assertIsString($result);
        self::assertFileExists($result);
        self::assertStringEqualsFile($result, $contents);
    }

    /** @return list<array{0: non-empty-string}> */
    public static function invalidTargetDirectoryValues(): array
    {
        return [
            [__DIR__ . '/not-a-directory'],
            [__DIR__ . '/not-a-directory/some-file.txt'],
        ];
    }

    /** @param non-empty-string $option */
    #[DataProvider('invalidTargetDirectoryValues')]
    public function testTheTargetDirectoryMustExist(string $option): void
    {
        $this->expectException(InvalidArgumentException::class);

        new RenameUpload(['target' => $option], new MoveUploadedFile());
    }

    #[DataProvider('fileProvider')]
    public function testExtensionFromOptionsIsUsedWhenUseUploadExtensionIsFalse(mixed $input): void
    {
        $directory = self::workDirectory() . '/target';
        if (! is_dir($directory)) {
            mkdir($directory);
        }
        $target = $directory . '/some-file.ext';

        $file = FileInformation::factory($input);
        self::assertFileExists($file->path);

        $filter = new RenameUpload([
            'target'               => $target,
            'use_upload_extension' => false,
            'randomize'            => true,
        ], new MoveUploadedFile());

        $result = $filter->__invoke($input);

        self::assertIsString($result);
        self::assertFileExists($result);

        self::assertStringEndsWith('.ext', $result);
        self::assertStringStartsWith('some-file', basename($result));
    }

    #[DataProvider('fileProvider')]
    public function testExtensionFromClientFilenameIsUsedWhenUseUploadExtensionIsTrue(mixed $input): void
    {
        $directory = self::workDirectory() . '/target';
        if (! is_dir($directory)) {
            mkdir($directory);
        }
        $target = $directory . '/some-file.ext';

        $file = FileInformation::factory($input);
        self::assertFileExists($file->path);

        $filter = new RenameUpload([
            'target'               => $target,
            'use_upload_extension' => true,
            'randomize'            => true,
        ], new MoveUploadedFile());

        $result = $filter->__invoke($input);

        self::assertIsString($result);
        self::assertFileExists($result);

        self::assertStringEndsWith('.txt', $result);
        self::assertStringStartsWith('some-file', basename($result));
    }

    #[DataProvider('fileProvider')]
    public function testOptionsAreCorrectlyPassedViaThePluginManager(mixed $input): void
    {
        $target = self::workDirectory() . '/target';
        if (! is_dir($target)) {
            mkdir($target);
        }

        $file = FileInformation::factory($input);
        self::assertFileExists($file->path);

        $expectFile = $target . '/' . $file->baseName;

        $pluginManager = new FilterPluginManager(new InMemoryContainer());

        $filter = $pluginManager->build(
            RenameUpload::class,
            ['target' => $target],
        );

        $result = $filter->__invoke($input);
        self::assertIsString($result);
        self::assertSame($expectFile, $result);

        self::assertFileExists($expectFile);
        self::assertFileDoesNotExist($file->path);

        unlink($result);
    }
}
