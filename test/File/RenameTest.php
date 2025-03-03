<?php

declare(strict_types=1);

namespace LaminasTest\Filter\File;

use Laminas\Filter\Exception;
use Laminas\Filter\File\Rename as FileRename;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use stdClass;

use function copy;
use function file_exists;
use function is_array;
use function mkdir;
use function preg_quote;
use function rmdir;
use function sprintf;
use function sys_get_temp_dir;
use function uniqid;
use function unlink;

use const DIRECTORY_SEPARATOR;

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
            rmdir(self::$tmpSubDirectoryPath);
        }

        if (self::$tmpPath !== null) {
            rmdir(self::$tmpPath);
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
        $oldFile    = self::getTempPath() . '/' . self::TEST_FILE_NAME;
        $newFile    = self::getTempPath() . '/new_file.xml';
        $newDir     = self::getTempSubDirectory();
        $newDirFile = self::getTempSubDirectory() . '/test_file.txt';

        return [
            'Configured with target file path and filtering file path'       => [
                'options'               => $newFile,
                'input'                 => $oldFile,
                'expectedGetFileResult' => [
                    'source'    => '*',
                    'target'    => $newFile,
                    'overwrite' => false,
                    'randomize' => false,
                ],
                'expectedFilterResult'  => $newFile,
            ],
            'Configured with target file path and filtering file path array' => [
                'options'               => $newFile,
                'input'                 => ['tmp_name' => $oldFile],
                'expectedGetFileResult' => [
                    'source'    => '*',
                    'target'    => $newFile,
                    'overwrite' => false,
                    'randomize' => false,
                ],
                'expectedFilterResult'  => ['tmp_name' => $newFile],
            ],
            'Configured with array'                                          => [
                'options'               => ['source' => $oldFile, 'target' => $newFile],
                'input'                 => $oldFile,
                'expectedGetFileResult' => [
                    'source'    => $oldFile,
                    'target'    => $newFile,
                    'overwrite' => false,
                    'randomize' => false,
                ],
                'expectedFilterResult'  => $newFile,
            ],
            'Configured with all options set'                                => [
                'options'               => [
                    'source'    => $oldFile,
                    'target'    => $newFile,
                    'overwrite' => true,
                    'randomize' => false,
                    'unknown'   => false,
                ],
                'input'                 => $oldFile,
                'expectedGetFileResult' => [
                    'source'    => $oldFile,
                    'target'    => $newFile,
                    'overwrite' => true,
                    'randomize' => false,
                ],
                'expectedFilterResult'  => $newFile,
            ],
            'Configured with array wrapped in array'                         => [
                'options'               => [0 => ['source' => $oldFile, 'target' => $newFile]],
                'input'                 => $oldFile,
                'expectedGetFileResult' => [
                    'source'    => $oldFile,
                    'target'    => $newFile,
                    'overwrite' => false,
                    'randomize' => false,
                ],
                'expectedFilterResult'  => $newFile,
            ],
            'Only target configured'                                         => [
                'options'               => ['target' => $newFile],
                'input'                 => $oldFile,
                'expectedGetFileResult' => [
                    'source'    => '*',
                    'target'    => $newFile,
                    'overwrite' => false,
                    'randomize' => false,
                ],
                'expectedFilterResult'  => $newFile,
            ],
            'Configured with target directory and filtering file path'       => [
                'options'               => $newDir,
                'input'                 => $oldFile,
                'expectedGetFileResult' => [
                    'source'    => '*',
                    'target'    => $newDir,
                    'overwrite' => false,
                    'randomize' => false,
                ],
                'expectedFilterResult'  => $newDirFile,
            ],
            'Configured with array and filtering file path'                  => [
                'options'               => ['source' => $oldFile, 'target' => $newDir],
                'input'                 => $oldFile,
                'expectedGetFileResult' => [
                    'source'    => $oldFile,
                    'target'    => $newDir,
                    'overwrite' => false,
                    'randomize' => false,
                ],
                'expectedFilterResult'  => $newDirFile,
            ],
            'Configured with array wrapped in array and filtering file path' => [
                'options'               => [0 => ['source' => $oldFile, 'target' => $newDir]],
                'input'                 => $oldFile,
                'expectedGetFileResult' => [
                    'source'    => $oldFile,
                    'target'    => $newDir,
                    'overwrite' => false,
                    'randomize' => false,
                ],
                'expectedFilterResult'  => $newDirFile,
            ],
            'Configured with only target and filtering file path'            => [
                'options'               => ['target' => $newDir],
                'input'                 => $oldFile,
                'expectedGetFileResult' => [
                    'source'    => '*',
                    'target'    => $newDir,
                    'overwrite' => false,
                    'randomize' => false,
                ],
                'expectedFilterResult'  => $newDirFile,
            ],
        ];
    }

    public static function returnInvalidFilterInputProvider(): array
    {
        $oldFile = self::getTempPath() . '/' . self::TEST_FILE_NAME;
        $newFile = self::getTempPath() . '/new_file.xml';

        return [
            'Source file non-existent' => [
                'options'               => $newFile,
                'input'                 => 'non-existent-file.txt',
                'expectedGetFileResult' => [
                    'source'    => '*',
                    'target'    => $newFile,
                    'overwrite' => false,
                    'randomize' => false,
                ],
                'expectedFilterResult'  => 'non-existent-file.txt',
            ],
            'Only source configured'   => [
                'options'               => ['source' => $oldFile],
                'input'                 => $oldFile,
                'expectedGetFileResult' => [
                    'source'    => $oldFile,
                    'target'    => '*',
                    'overwrite' => false,
                    'randomize' => false,
                ],
                'expectedFilterResult'  => $oldFile,
            ],
        ];
    }

    #[DataProvider('returnValidFilterInputProvider')]
    #[DataProvider('returnInvalidFilterInputProvider')]
    public function testFilterValidPaths(
        string|array $options,
        string|array $input,
        array $expectedGetFileResult,
        string|array $expectedFilterResult
    ): void {
        self::createSourceFile();

        $filter = new FileRename($options);

        self::assertEquals([$expectedGetFileResult], $filter->getFile());

        try {
            self::assertSame($expectedFilterResult, $filter->filter($input));
        } finally {
            /** @var string $fileToRemove */
            $fileToRemove = is_array($expectedFilterResult) ? $expectedFilterResult['tmp_name'] : $expectedFilterResult;

            if (file_exists($fileToRemove)) {
                unlink($fileToRemove);
            }
        }
    }

    public function testAddSameFileAgainAndOverwriteExistingTarget(): void
    {
        self::createSourceFile();

        $oldFile = self::getTempPath() . '/' . self::TEST_FILE_NAME;
        $newFile = self::getTempPath() . '/new_file.xml';

        $filter = new FileRename([
            'source' => $oldFile,
            'target' => '/to-be-overwritten.xml',
        ]);

        $filter->addFile([
            'source' => $oldFile,
            'target' => $newFile,
        ]);

        self::assertSame(
            [
                0 => [
                    'source'    => $oldFile,
                    'target'    => $newFile,
                    'overwrite' => false,
                    'randomize' => false,
                ],
            ],
            $filter->getFile()
        );

        try {
            self::assertSame($newFile, $filter($oldFile));
        } finally {
            if (file_exists($newFile)) {
                unlink($newFile);
            }
        }
    }

    public function testGetNewName(): void
    {
        self::createSourceFile();

        $oldFile = self::getTempPath() . '/' . self::TEST_FILE_NAME;
        $newDir  = self::getTempSubDirectory();

        $filter = new FileRename([
            'source' => $oldFile,
            'target' => $newDir,
        ]);

        self::assertSame(
            [
                0 => [
                    'source'    => $oldFile,
                    'target'    => $newDir,
                    'overwrite' => false,
                    'randomize' => false,
                ],
            ],
            $filter->getFile()
        );

        self::assertSame($newDir . '/' . self::TEST_FILE_NAME, $filter->getNewName($oldFile));
    }

    public function testGetNewNameExceptionWithExistingFile(): void
    {
        self::createSourceFile();
        $oldFile = self::getTempPath() . '/' . self::TEST_FILE_NAME;
        $newFile = self::getTempPath() . '/new_file.xml';

        $filter = new FileRename([
            'source' => $oldFile,
            'target' => $newFile,
        ]);

        copy($oldFile, $newFile);

        self::assertSame(
            [
                0 => [
                    'source'    => $oldFile,
                    'target'    => $newFile,
                    'overwrite' => false,
                    'randomize' => false,
                ],
            ],
            $filter->getFile()
        );
        $this->expectException(Exception\InvalidArgumentException::class);
        $this->expectExceptionMessage('could not be renamed');

        try {
            self::assertSame($newFile, $filter->getNewName($oldFile));
        } finally {
            if (file_exists($newFile)) {
                unlink($newFile);
            }
        }
    }

    public function testGetNewNameOverwriteWithExistingFile(): void
    {
        self::createSourceFile();
        $oldFile = self::getTempPath() . '/' . self::TEST_FILE_NAME;
        $newFile = self::getTempPath() . '/new_file.xml';

        $filter = new FileRename([
            'source'    => $oldFile,
            'target'    => $newFile,
            'overwrite' => true,
        ]);

        copy($oldFile, $newFile);

        self::assertSame(
            [
                0 => [
                    'source'    => $oldFile,
                    'target'    => $newFile,
                    'overwrite' => true,
                    'randomize' => false,
                ],
            ],
            $filter->getFile()
        );
        self::assertSame($newFile, $filter->getNewName($oldFile));
    }

    public function testGetRandomizedFile(): void
    {
        self::createSourceFile();
        $oldFile = self::getTempPath() . '/' . self::TEST_FILE_NAME;
        $newFile = self::getTempPath() . '/new_file.xml';

        $filter = new FileRename([
            'source'    => $oldFile,
            'target'    => $newFile,
            'randomize' => true,
        ]);

        self::assertSame(
            [
                0 => [
                    'source'    => $oldFile,
                    'target'    => $newFile,
                    'randomize' => true,
                    'overwrite' => false,
                ],
            ],
            $filter->getFile()
        );
        $fileNoExt = self::getTempPath() . '/new_file';
        self::assertMatchesRegularExpression(
            '#' . preg_quote($fileNoExt) . '_.{13}\.xml#',
            $filter->getNewName($oldFile)
        );
    }

    public function testGetRandomizedFileWithoutExtension(): void
    {
        self::createSourceFile();

        $oldFile   = self::getTempPath() . '/' . self::TEST_FILE_NAME;
        $fileNoExt = self::getTempPath() . '/new_file';
        $filter    = new FileRename([
            'source'    => $oldFile,
            'target'    => $fileNoExt,
            'randomize' => true,
        ]);

        self::assertSame(
            [
                0 => [
                    'source'    => $oldFile,
                    'target'    => $fileNoExt,
                    'randomize' => true,
                    'overwrite' => false,
                ],
            ],
            $filter->getFile()
        );
        self::assertMatchesRegularExpression(
            '#' . preg_quote($fileNoExt) . '_.{13}#',
            $filter->getNewName($oldFile)
        );
    }

    public function testAddFileWithString(): void
    {
        self::createSourceFile();

        $oldFile = self::getTempPath() . '/' . self::TEST_FILE_NAME;
        $newFile = self::getTempPath() . '/new_file.xml';

        $filter = new FileRename($oldFile);
        $filter->addFile($newFile);

        self::assertSame(
            [
                0 => [
                    'target'    => $newFile,
                    'source'    => '*',
                    'overwrite' => false,
                    'randomize' => false,
                ],
            ],
            $filter->getFile()
        );
        try {
            self::assertSame($newFile, $filter($oldFile));
        } finally {
            if (file_exists($newFile)) {
                unlink($newFile);
            }
        }
    }

    public function testAddFileWithInvalidOption(): void
    {
        $filter = new FileRename('invalid');
        $this->expectException(Exception\InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid options');
        $filter->addFile(1234);
    }

    public function testInvalidConstruction(): void
    {
        $this->expectException(Exception\InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid options');
        new FileRename(1234);
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
    public function testReturnUnfiltered(mixed $input): void
    {
        self::createSourceFile();

        $filter = new FileRename(self::getTempPath() . '/new_file.xml');

        self::assertSame($input, $filter($input));
    }
}
