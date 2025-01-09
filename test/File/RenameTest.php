<?php

declare(strict_types=1);

namespace LaminasTest\Filter\File;

use Laminas\Filter\Exception\InvalidArgumentException;
use Laminas\Filter\File\Rename as FileRename;
use LaminasTest\Filter\Compress\TmpDirectory;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use stdClass;
use Throwable;

use function copy;
use function file_exists;
use function mkdir;
use function preg_quote;
use function rmdir;
use function sprintf;
use function sys_get_temp_dir;
use function uniqid;
use function unlink;

use const DIRECTORY_SEPARATOR;

/**
 * @psalm-import-type Options from FileRename
 */
final class RenameTest extends TestCase
{
    private const TEST_FILE_NAME = 'test_file.txt';

    private static ?string $tmpPath             = null;
    private static ?string $tmpSubDirectoryPath = null;

    private static function getTempPath(): string
    {
        if (self::$tmpPath === null) {
            self::$tmpPath = sys_get_temp_dir() . '/' . uniqid('laminasfilter');
            mkdir(self::$tmpPath, 0775, true);
        }

        return self::$tmpPath;
    }

    private static function getTempSubDirectory(): string
    {
        if (self::$tmpSubDirectoryPath === null) {
            self::$tmpSubDirectoryPath = self::getTempPath() . '/test_dir';
            mkdir(self::$tmpSubDirectoryPath, 0775, true);
        }

        return self::$tmpSubDirectoryPath;
    }

    public static function tearDownAfterClass(): void
    {
        if (self::$tmpSubDirectoryPath !== null) {
            TmpDirectory::cleanUp(self::$tmpSubDirectoryPath);
        }

        if (self::$tmpPath !== null) {
            TmpDirectory::cleanUp(self::$tmpPath);
        }
    }

    private static function createSourceFile(): void
    {
        copy(__DIR__ . '/../_files/testfile.txt', self::getTempPath() . '/' . self::TEST_FILE_NAME);
    }

    private static function cleanupSourceFile(): void
    {
        $fileToRemove = self::getTempPath() . '/' . self::TEST_FILE_NAME;
        if (file_exists($fileToRemove)) {
            unlink($fileToRemove);
        }
    }

    public function tearDown(): void
    {
        self::cleanupSourceFile();
    }

    public static function returnValidFilterInputProvider(): array
    {
        $oldFilePath  = self::getTempPath() . '/' . self::TEST_FILE_NAME;
        $newFileName  = 'new_file.xml';
        $newDirectory = self::getTempSubDirectory();

        return [
            'Rename in place'                           => [
                'options'              => ['match' => $oldFilePath, 'rename_to' => $newFileName],
                'input'                => $oldFilePath,
                'expectedFilterResult' => self::getTempPath() . '/' . $newFileName,
            ],
            'Move to new directory'                     => [
                'options'              => ['match' => $oldFilePath, 'target_directory' => $newDirectory],
                'input'                => $oldFilePath,
                'expectedFilterResult' => $newDirectory . '/' . self::TEST_FILE_NAME,
            ],
            'Move to new directory and rename'          => [
                'options'              => [
                    'match'            => $oldFilePath,
                    'target_directory' => $newDirectory,
                    'rename_to'        => $newFileName,
                ],
                'input'                => $oldFilePath,
                'expectedFilterResult' => $newDirectory . '/' . $newFileName,
            ],
            'No replacement or move configured'         => [
                'options'              => [],
                'input'                => $oldFilePath,
                'expectedFilterResult' => $oldFilePath,
            ],
            'Rename in place with wildcard match'       => [
                'options'              => ['rename_to' => $newFileName],
                'input'                => $oldFilePath,
                'expectedFilterResult' => self::getTempPath() . '/' . $newFileName,
            ],
            'Move to new directory with wildcard match' => [
                'options'              => ['target_directory' => $newDirectory],
                'input'                => $oldFilePath,
                'expectedFilterResult' => $newDirectory . '/' . self::TEST_FILE_NAME,
            ],
            'Match with single character wild'          => [
                'options'              => [
                    'match'     => self::getTempPath() . '/test_fil?.txt',
                    'rename_to' => $newFileName,
                ],
                'input'                => $oldFilePath,
                'expectedFilterResult' => self::getTempPath() . '/' . $newFileName,
            ],
            'Array of options'                          => [
                'options'              => [
                    [
                        'match'            => $oldFilePath,
                        'target_directory' => $newDirectory,
                        'rename_to'        => $newFileName,
                    ],
                ],
                'input'                => $oldFilePath,
                'expectedFilterResult' => $newDirectory . '/' . $newFileName,
            ],
            'Array of multiple options with one match'  => [
                'options'              => [
                    [
                        'match'     => self::getTempPath() . '/no_match.txt',
                        'rename_to' => 'failed_if_this.txt',
                    ],
                    [
                        'match'            => $oldFilePath,
                        'target_directory' => $newDirectory,
                        'rename_to'        => $newFileName,
                    ],
                ],
                'input'                => $oldFilePath,
                'expectedFilterResult' => $newDirectory . '/' . $newFileName,
            ],
        ];
    }

    /**
     * @param Options $options
     */
    #[DataProvider('returnValidFilterInputProvider')]
    public function testFilterValidPaths(
        array $options,
        string $input,
        string $expectedFilterResult
    ): void {
        self::createSourceFile();

        $filter = new FileRename($options);

        try {
            self::assertSame($expectedFilterResult, $filter->filter($input));
            self::assertFileExists($expectedFilterResult);
        } finally {
            if (file_exists($expectedFilterResult)) {
                unlink($expectedFilterResult);
            }
        }
    }

    public static function returnInvalidFilterInputProvider(): array
    {
        $oldFilePath = self::getTempPath() . '/' . self::TEST_FILE_NAME;

        return [
            'Source file non-existent' => [
                'options' => ['rename_to' => 'new_file.xml'],
                'input'   => 'non-existent-file.txt',
            ],
            'Only match configured'    => [
                'options' => ['match' => $oldFilePath],
                'input'   => $oldFilePath,
            ],
        ];
    }

    /**
     * @param Options $options
     */
    #[DataProvider('returnInvalidFilterInputProvider')]
    public function testFilterInvalidPaths(
        array $options,
        string $input,
    ): void {
        self::createSourceFile();

        $filter = new FileRename($options);

        self::assertSame($input, $filter->filter($input));
    }

    public function testOverwriteTrue(): void
    {
        self::createSourceFile();

        $oldFile         = self::getTempPath() . '/' . self::TEST_FILE_NAME;
        $newFile         = 'new_file.xml';
        $expectedNewPath = self::getTempPath() . '/' . $newFile;

        $filter = new FileRename(['rename_to' => $newFile, 'overwrite' => true]);

        try {
            self::assertSame($expectedNewPath, $filter->filter($oldFile));
            self::assertFileExists($expectedNewPath);

            self::createSourceFile();
            self::assertSame($expectedNewPath, $filter->filter($oldFile));
            self::assertFileExists($expectedNewPath);
        } finally {
            if (file_exists($expectedNewPath)) {
                unlink($expectedNewPath);
            }
        }
    }

    public function testOverwriteFalseThrowsExceptionWithPreExistingTarget(): void
    {
        self::createSourceFile();

        $oldFile         = self::getTempPath() . '/' . self::TEST_FILE_NAME;
        $newFile         = 'new_file.xml';
        $expectedNewPath = self::getTempPath() . '/' . $newFile;

        $filter = new FileRename(['rename_to' => $newFile]);

        try {
            self::assertSame($expectedNewPath, $filter->filter($oldFile));
            self::assertFileExists($expectedNewPath);

            self::createSourceFile();

            $this->expectException(InvalidArgumentException::class);
            $this->expectExceptionMessage(
                sprintf(
                    '"File "%s" could not be renamed to "%s"; target file already exists',
                    $oldFile,
                    $expectedNewPath
                )
            );

            $filter->filter($oldFile);
        } finally {
            if (file_exists($expectedNewPath)) {
                unlink($expectedNewPath);
            }
        }
    }

    public function testGetRandomizedFile(): void
    {
        self::createSourceFile();
        $oldFile = self::getTempPath() . '/' . self::TEST_FILE_NAME;

        $filter = new FileRename(['rename_to' => 'new_file.xml', 'randomize' => true]);

        $fileNoExt = self::getTempPath() . '/new_file';

        try {
            $result = $filter->filter($oldFile);

            self::assertMatchesRegularExpression(
                '#' . preg_quote($fileNoExt) . '_.{13}\.xml#',
                $result
            );
        } finally {
            if (isset($result) && file_exists($result)) {
                unlink($result);
            }
        }
    }

    public function testGetRandomizedFileWithoutExtension(): void
    {
        self::createSourceFile();
        $oldFile   = self::getTempPath() . '/' . self::TEST_FILE_NAME;
        $fileNoExt = self::getTempPath() . '/new_file';

        $filter = new FileRename(['rename_to' => 'new_file', 'randomize' => true]);

        try {
            $result = $filter->filter($oldFile);

            self::assertMatchesRegularExpression(
                '#' . preg_quote($fileNoExt) . '_.{13}#',
                $result
            );
        } finally {
            if (isset($result) && file_exists($result)) {
                unlink($result);
            }
        }
    }

    /** @return list<array{0: mixed}> */
    public static function returnUnfilteredDataProvider(): array
    {
        $oldFile  = self::getTempPath() . '/' . self::TEST_FILE_NAME;
        $origFile = sprintf('%s%soriginal.file', self::getTempPath(), DIRECTORY_SEPARATOR);

        return [
            [null],
            [new stdClass()],
            [
                [
                    $oldFile,
                    $origFile,
                ],
            ],
        ];
    }

    #[DataProvider('returnUnfilteredDataProvider')]
    public function testInvalidFilterInputIsReturnedUnprocessed(mixed $input): void
    {
        self::createSourceFile();

        $filter = new FileRename(['rename_to' => 'new_file.xml']);

        self::assertSame($input, $filter($input));
    }

    public function testTargetIsNotADirectory(): void
    {
        $targetDirectory = self::getTempPath() . '/not-a-directory';

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(sprintf('The target directory "%s" does not exist', $targetDirectory));

        new FileRename(['target_directory' => $targetDirectory]);
    }

    public function testTargetDirectoryIsNotWritable(): void
    {
        $targetDirectory = self::getTempPath() . '/not-writable';

        mkdir($targetDirectory, 0555);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(sprintf('The target directory "%s" is not writable', $targetDirectory));

        try {
            new FileRename(['target_directory' => $targetDirectory]);
        } catch (Throwable $e) {
            rmdir($targetDirectory);
            throw $e;
        }
    }
}
