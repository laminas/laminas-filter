<?php

declare(strict_types=1);

namespace LaminasTest\Filter\File;

use Laminas\Filter\Exception;
use Laminas\Filter\File\Rename as FileRename;
use PHPUnit\Framework\TestCase;
use stdClass;

use function copy;
use function dirname;
use function file_exists;
use function is_dir;
use function mkdir;
use function preg_quote;
use function rmdir;
use function sprintf;
use function sys_get_temp_dir;
use function uniqid;
use function unlink;

use const DIRECTORY_SEPARATOR;

class RenameTest extends TestCase
{
    /**
     * Path to test files
     *
     * @var string
     */
    protected $tmpPath;

    /**
     * Original testfile
     *
     * @var string
     */
    protected $origFile;

    /**
     * Testfile
     *
     * @var string
     */
    protected $oldFile;

    /**
     * Testfile
     *
     * @var string
     */
    protected $newFile;

    /**
     * Testdirectory
     *
     * @var string
     */
    protected $newDir;

    /**
     * Testfile in Testdirectory
     *
     * @var string
     */
    protected $newDirFile;

    /**
     * Sets the path to test files
     */
    public function setUp(): void
    {
        $control       = sprintf('%s/_files/testfile.txt', dirname(__DIR__));
        $this->tmpPath = sprintf('%s%s%s', sys_get_temp_dir(), DIRECTORY_SEPARATOR, uniqid('laminasilter'));
        mkdir($this->tmpPath, 0775, true);

        $this->oldFile    = sprintf('%s%stestfile.txt', $this->tmpPath, DIRECTORY_SEPARATOR);
        $this->origFile   = sprintf('%s%soriginal.file', $this->tmpPath, DIRECTORY_SEPARATOR);
        $this->newFile    = sprintf('%s%snewfile.xml', $this->tmpPath, DIRECTORY_SEPARATOR);
        $this->newDir     = sprintf('%s%stestdir', $this->tmpPath, DIRECTORY_SEPARATOR);
        $this->newDirFile = sprintf('%s%stestfile.txt', $this->newDir, DIRECTORY_SEPARATOR);

        copy($control, $this->oldFile);
        copy($control, $this->origFile);
        mkdir($this->newDir, 0775, true);
    }

    /**
     * Sets the path to test files
     */
    public function tearDown(): void
    {
        if (is_dir($this->tmpPath)) {
            if (file_exists($this->oldFile)) {
                unlink($this->oldFile);
            }
            if (file_exists($this->origFile)) {
                unlink($this->origFile);
            }
            if (file_exists($this->newFile)) {
                unlink($this->newFile);
            }
            if (is_dir($this->newDir)) {
                if (file_exists($this->newDirFile)) {
                    unlink($this->newDirFile);
                }
                rmdir($this->newDir);
            }
            rmdir($this->tmpPath);
        }
    }

    /**
     * Test single parameter filter
     */
    public function testConstructSingleValue(): void
    {
        $filter = new FileRename($this->newFile);

        $this->assertSame(
            [
                0 => [
                    'target'    => $this->newFile,
                    'source'    => '*',
                    'overwrite' => false,
                    'randomize' => false,
                ],
            ],
            $filter->getFile()
        );
        $this->assertSame($this->newFile, $filter($this->oldFile));
        $this->assertSame('falsefile', $filter('falsefile'));
    }

    /**
     * Test single parameter filter
     */
    public function testConstructSingleValueWithFilesArray(): void
    {
        $filter = new FileRename($this->newFile);

        $this->assertSame(
            [
                0 => [
                    'target'    => $this->newFile,
                    'source'    => '*',
                    'overwrite' => false,
                    'randomize' => false,
                ],
            ],
            $filter->getFile()
        );
        $this->assertSame(
            ['tmp_name' => $this->newFile],
            $filter(['tmp_name' => $this->oldFile])
        );
        $this->assertSame('falsefile', $filter('falsefile'));
    }

    /**
     * Test single array parameter filter
     */
    public function testConstructSingleArray(): void
    {
        $filter = new FileRename([
            'source' => $this->oldFile,
            'target' => $this->newFile,
        ]);

        $this->assertSame(
            [
                0 => [
                    'source'    => $this->oldFile,
                    'target'    => $this->newFile,
                    'overwrite' => false,
                    'randomize' => false,
                ],
            ],
            $filter->getFile()
        );
        $this->assertSame($this->newFile, $filter($this->oldFile));
        $this->assertSame('falsefile', $filter('falsefile'));
    }

    /**
     * Test full array parameter filter
     */
    public function testConstructFullOptionsArray(): void
    {
        $filter = new FileRename([
            'source'    => $this->oldFile,
            'target'    => $this->newFile,
            'overwrite' => true,
            'randomize' => false,
            'unknown'   => false,
        ]);

        $this->assertSame(
            [
                0 => [
                    'source'    => $this->oldFile,
                    'target'    => $this->newFile,
                    'overwrite' => true,
                    'randomize' => false,
                ],
            ],
            $filter->getFile()
        );
        $this->assertSame($this->newFile, $filter($this->oldFile));
        $this->assertSame('falsefile', $filter('falsefile'));
    }

    /**
     * Test single array parameter filter
     */
    public function testConstructDoubleArray(): void
    {
        $filter = new FileRename([
            0 => [
                'source' => $this->oldFile,
                'target' => $this->newFile,
            ],
        ]);

        $this->assertSame(
            [
                0 => [
                    'source'    => $this->oldFile,
                    'target'    => $this->newFile,
                    'overwrite' => false,
                    'randomize' => false,
                ],
            ],
            $filter->getFile()
        );
        $this->assertSame($this->newFile, $filter($this->oldFile));
        $this->assertSame('falsefile', $filter('falsefile'));
    }

    /**
     * Test single array parameter filter
     */
    public function testConstructTruncatedTarget(): void
    {
        $filter = new FileRename([
            'source' => $this->oldFile,
        ]);

        $this->assertSame(
            [
                0 => [
                    'source'    => $this->oldFile,
                    'target'    => '*',
                    'overwrite' => false,
                    'randomize' => false,
                ],
            ],
            $filter->getFile()
        );
        $this->assertSame($this->oldFile, $filter($this->oldFile));
        $this->assertSame('falsefile', $filter('falsefile'));
    }

    /**
     * Test single array parameter filter
     */
    public function testConstructTruncatedSource(): void
    {
        $filter = new FileRename([
            'target' => $this->newFile,
        ]);

        $this->assertSame(
            [
                0 => [
                    'target'    => $this->newFile,
                    'source'    => '*',
                    'overwrite' => false,
                    'randomize' => false,
                ],
            ],
            $filter->getFile()
        );
        $this->assertSame($this->newFile, $filter($this->oldFile));
        $this->assertSame('falsefile', $filter('falsefile'));
    }

    /**
     * Test single parameter filter by using directory only
     */
    public function testConstructSingleDirectory(): void
    {
        $filter = new FileRename($this->newDir);

        $this->assertSame(
            [
                0 => [
                    'target'    => $this->newDir,
                    'source'    => '*',
                    'overwrite' => false,
                    'randomize' => false,
                ],
            ],
            $filter->getFile()
        );
        $this->assertSame($this->newDirFile, $filter($this->oldFile));
        $this->assertSame('falsefile', $filter('falsefile'));
    }

    /**
     * Test single array parameter filter by using directory only
     */
    public function testConstructSingleArrayDirectory(): void
    {
        $filter = new FileRename([
            'source' => $this->oldFile,
            'target' => $this->newDir,
        ]);

        $this->assertSame(
            [
                0 => [
                    'source'    => $this->oldFile,
                    'target'    => $this->newDir,
                    'overwrite' => false,
                    'randomize' => false,
                ],
            ],
            $filter->getFile()
        );
        $this->assertSame($this->newDirFile, $filter($this->oldFile));
        $this->assertSame('falsefile', $filter('falsefile'));
    }

    /**
     * Test single array parameter filter by using directory only
     */
    public function testConstructDoubleArrayDirectory(): void
    {
        $filter = new FileRename([
            0 => [
                'source' => $this->oldFile,
                'target' => $this->newDir,
            ],
        ]);

        $this->assertSame(
            [
                0 => [
                    'source'    => $this->oldFile,
                    'target'    => $this->newDir,
                    'overwrite' => false,
                    'randomize' => false,
                ],
            ],
            $filter->getFile()
        );
        $this->assertSame($this->newDirFile, $filter($this->oldFile));
        $this->assertSame('falsefile', $filter('falsefile'));
    }

    /**
     * Test single array parameter filter by using directory only
     */
    public function testConstructTruncatedSourceDirectory(): void
    {
        $filter = new FileRename([
            'target' => $this->newDir,
        ]);

        $this->assertSame(
            [
                0 => [
                    'target'    => $this->newDir,
                    'source'    => '*',
                    'overwrite' => false,
                    'randomize' => false,
                ],
            ],
            $filter->getFile()
        );
        $this->assertSame($this->newDirFile, $filter($this->oldFile));
        $this->assertSame('falsefile', $filter('falsefile'));
    }

    public function testAddSameFileAgainAndOverwriteExistingTarget(): void
    {
        $filter = new FileRename([
            'source' => $this->oldFile,
            'target' => $this->newDir,
        ]);

        $filter->addFile([
            'source' => $this->oldFile,
            'target' => $this->newFile,
        ]);

        $this->assertSame(
            [
                0 => [
                    'source'    => $this->oldFile,
                    'target'    => $this->newFile,
                    'overwrite' => false,
                    'randomize' => false,
                ],
            ],
            $filter->getFile()
        );
        $this->assertSame($this->newFile, $filter($this->oldFile));
        $this->assertSame('falsefile', $filter('falsefile'));
    }

    public function testGetNewName(): void
    {
        $filter = new FileRename([
            'source' => $this->oldFile,
            'target' => $this->newDir,
        ]);

        $this->assertSame(
            [
                0 => [
                    'source'    => $this->oldFile,
                    'target'    => $this->newDir,
                    'overwrite' => false,
                    'randomize' => false,
                ],
            ],
            $filter->getFile()
        );
        $this->assertSame($this->newDirFile, $filter->getNewName($this->oldFile));
    }

    public function testGetNewNameExceptionWithExistingFile(): void
    {
        $filter = new FileRename([
            'source' => $this->oldFile,
            'target' => $this->newFile,
        ]);

        copy($this->oldFile, $this->newFile);

        $this->assertSame(
            [
                0 => [
                    'source'    => $this->oldFile,
                    'target'    => $this->newFile,
                    'overwrite' => false,
                    'randomize' => false,
                ],
            ],
            $filter->getFile()
        );
        $this->expectException(Exception\InvalidArgumentException::class);
        $this->expectExceptionMessage('could not be renamed');
        $this->assertSame($this->newFile, $filter->getNewName($this->oldFile));
    }

    public function testGetNewNameOverwriteWithExistingFile(): void
    {
        $filter = new FileRename([
            'source'    => $this->oldFile,
            'target'    => $this->newFile,
            'overwrite' => true,
        ]);

        copy($this->oldFile, $this->newFile);

        $this->assertSame(
            [
                0 => [
                    'source'    => $this->oldFile,
                    'target'    => $this->newFile,
                    'overwrite' => true,
                    'randomize' => false,
                ],
            ],
            $filter->getFile()
        );
        $this->assertSame($this->newFile, $filter->getNewName($this->oldFile));
    }

    public function testGetRandomizedFile(): void
    {
        $filter = new FileRename([
            'source'    => $this->oldFile,
            'target'    => $this->newFile,
            'randomize' => true,
        ]);

        $this->assertSame(
            [
                0 => [
                    'source'    => $this->oldFile,
                    'target'    => $this->newFile,
                    'randomize' => true,
                    'overwrite' => false,
                ],
            ],
            $filter->getFile()
        );
        $fileNoExt = $this->tmpPath . DIRECTORY_SEPARATOR . 'newfile';
        $this->assertMatchesRegularExpression(
            '#' . preg_quote($fileNoExt) . '_.{13}\.xml#',
            $filter->getNewName($this->oldFile)
        );
    }

    public function testGetRandomizedFileWithoutExtension(): void
    {
        $fileNoExt = $this->tmpPath . DIRECTORY_SEPARATOR . 'newfile';
        $filter    = new FileRename([
            'source'    => $this->oldFile,
            'target'    => $fileNoExt,
            'randomize' => true,
        ]);

        $this->assertSame(
            [
                0 => [
                    'source'    => $this->oldFile,
                    'target'    => $fileNoExt,
                    'randomize' => true,
                    'overwrite' => false,
                ],
            ],
            $filter->getFile()
        );
        $this->assertMatchesRegularExpression(
            '#' . preg_quote($fileNoExt) . '_.{13}#',
            $filter->getNewName($this->oldFile)
        );
    }

    public function testAddFileWithString(): void
    {
        $filter = new FileRename($this->oldFile);
        $filter->addFile($this->newFile);

        $this->assertSame(
            [
                0 => [
                    'target'    => $this->newFile,
                    'source'    => '*',
                    'overwrite' => false,
                    'randomize' => false,
                ],
            ],
            $filter->getFile()
        );
        $this->assertSame($this->newFile, $filter($this->oldFile));
        $this->assertSame('falsefile', $filter('falsefile'));
    }

    public function testAddFileWithInvalidOption(): void
    {
        $filter = new FileRename($this->oldFile);
        $this->expectException(Exception\InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid options');
        $filter->addFile(1234);
    }

    public function testInvalidConstruction(): void
    {
        $this->expectException(Exception\InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid options');
        $filter = new FileRename(1234);
    }

    public function returnUnfilteredDataProvider()
    {
        return [
            [null],
            [new stdClass()],
            [
                [
                    $this->oldFile,
                    $this->origFile,
                ],
            ],
        ];
    }

    /**
     * @dataProvider returnUnfilteredDataProvider
     */
    public function testReturnUnfiltered($input): void
    {
        $filter = new FileRename($this->newFile);

        $this->assertSame($input, $filter($input));
    }
}
