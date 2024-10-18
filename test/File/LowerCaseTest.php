<?php

declare(strict_types=1);

namespace LaminasTest\Filter\File;

use Laminas\Filter\Exception\InvalidArgumentException;
use Laminas\Filter\File\LowerCase as FileLowerCase;
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

class LowerCaseTest extends TestCase
{
    private string $testFile;

    /**
     * Sets the path to test files
     */
    public function setUp(): void
    {
        $source         = dirname(__DIR__) . '/_files/testfile2.txt';
        $this->testFile = sprintf('%s/%s.txt', sys_get_temp_dir(), uniqid('laminasilter'));
        copy($source, $this->testFile);
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
        $filter = new FileLowerCase();
        $filter($this->testFile);
        self::assertStringContainsString('this is a file', file_get_contents($this->testFile));
    }

    public function testNormalWorkflowWithFilesArray(): void
    {
        self::assertStringContainsString('This is a File', file_get_contents($this->testFile));
        $filter = new FileLowerCase();
        $filter(['tmp_name' => $this->testFile]);
        self::assertStringContainsString('this is a file', file_get_contents($this->testFile));
    }

    public function testFileNotFoundException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('not found');
        $filter = new FileLowerCase();
        $filter($this->testFile . 'unknown');
    }

    public function testCheckSettingOfEncodingInInstance(): void
    {
        self::assertStringContainsString('This is a File', file_get_contents($this->testFile));
        $filter = new FileLowerCase(['encoding' => 'ISO-8859-1']);
        $filter($this->testFile);
        self::assertStringContainsString('this is a file', file_get_contents($this->testFile));
    }

    public static function returnUnfilteredDataProvider(): array
    {
        return [
            [null],
            [new stdClass()],
            [
                [
                    sprintf('%s/%s.txt', sys_get_temp_dir(), uniqid()),
                    sprintf('%s/%s.txt', sys_get_temp_dir(), uniqid()),
                ],
            ],
        ];
    }

    #[DataProvider('returnUnfilteredDataProvider')]
    public function testReturnUnfiltered(mixed $input): void
    {
        $filter = new FileLowerCase();

        self::assertSame($input, $filter($input));
    }
}
