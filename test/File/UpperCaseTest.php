<?php

declare(strict_types=1);

namespace LaminasTest\Filter\File;

use Laminas\Filter\Exception\InvalidArgumentException;
use Laminas\Filter\File\UpperCase as FileUpperCase;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use stdClass;

use function copy;
use function dirname;
use function file_exists;
use function file_get_contents;
use function sprintf;
use function sys_get_temp_dir;
use function uniqid;
use function unlink;

use const DIRECTORY_SEPARATOR;

class UpperCaseTest extends TestCase
{
    private string $testFile;

    private static function testFilePath(): string
    {
        return sprintf('%s/%s.txt', sys_get_temp_dir(), uniqid('laminas-filter'));
    }

    private static function createTestFile(string $filePath): void
    {
        $filesPath = dirname(__DIR__) . DIRECTORY_SEPARATOR . '_files' . DIRECTORY_SEPARATOR;
        $origFile  = $filesPath . 'testfile2.txt';
        copy($origFile, $filePath);
    }

    /**
     * Sets the path to test files
     */
    public function setUp(): void
    {
        $this->testFile = self::testFilePath();
        self::createTestFile($this->testFile);
    }

    /**
     * Sets the path to test files
     */
    public function tearDown(): void
    {
        if (file_exists($this->testFile)) {
            unlink($this->testFile);
        }
    }

    public function testInstanceCreationAndNormalWorkflow(): void
    {
        self::assertStringContainsString('This is a File', file_get_contents($this->testFile));
        $filter = new FileUpperCase();
        $filter($this->testFile);
        self::assertStringContainsString('THIS IS A FILE', file_get_contents($this->testFile));
    }

    public function testNormalWorkflowWithFilesArray(): void
    {
        self::assertStringContainsString('This is a File', file_get_contents($this->testFile));
        $filter = new FileUpperCase();
        $filter(['tmp_name' => $this->testFile]);
        self::assertStringContainsString('THIS IS A FILE', file_get_contents($this->testFile));
    }

    public function testFileNotFoundException(): void
    {
        $filter = new FileUpperCase();
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('not found');
        $filter($this->testFile . 'unknown');
    }

    public function testCheckSettingOfEncodingInInstance(): void
    {
        self::assertStringContainsString('This is a File', file_get_contents($this->testFile));
        $filter = new FileUpperCase(['encoding' => 'ISO-8859-1']);
        $filter($this->testFile);
        self::assertStringContainsString('THIS IS A FILE', file_get_contents($this->testFile));
    }

    public static function returnUnfilteredDataProvider(): array
    {
        return [
            [null],
            [new stdClass()],
            [
                [
                    self::testFilePath(),
                    'something invalid',
                ],
            ],
        ];
    }

    #[DataProvider('returnUnfilteredDataProvider')]
    public function testReturnUnfiltered(mixed $input): void
    {
        $filter = new FileUpperCase(['encoding' => 'ISO-8859-1']);

        self::assertSame($input, $filter($input));
    }
}
