<?php

declare(strict_types=1);

namespace LaminasTest\Filter\File;

use Laminas\Filter\Exception;
use Laminas\Filter\Exception\ExtensionNotLoadedException;
use Laminas\Filter\File\UpperCase as FileUpperCase;
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
    /**
     * Testfile
     *
     * @var string
     */
    protected $testFile;

    /**
     * Sets the path to test files
     */
    public function setUp(): void
    {
        $filesPath      = dirname(__DIR__) . DIRECTORY_SEPARATOR . '_files' . DIRECTORY_SEPARATOR;
        $origFile       = $filesPath . 'testfile2.txt';
        $this->testFile = sprintf('%s/%s.txt', sys_get_temp_dir(), uniqid('laminasilter'));

        copy($origFile, $this->testFile);
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

    /**
     * @return void
     */
    public function testInstanceCreationAndNormalWorkflow()
    {
        $this->assertStringContainsString('This is a File', file_get_contents($this->testFile));
        $filter = new FileUpperCase();
        $filter($this->testFile);
        $this->assertStringContainsString('THIS IS A FILE', file_get_contents($this->testFile));
    }

    /**
     * @return void
     */
    public function testNormalWorkflowWithFilesArray()
    {
        $this->assertStringContainsString('This is a File', file_get_contents($this->testFile));
        $filter = new FileUpperCase();
        $filter(['tmp_name' => $this->testFile]);
        $this->assertStringContainsString('THIS IS A FILE', file_get_contents($this->testFile));
    }

    /**
     * @return void
     */
    public function testFileNotFoundException()
    {
        $filter = new FileUpperCase();
        $this->expectException(Exception\InvalidArgumentException::class);
        $this->expectExceptionMessage('not found');
        $filter($this->testFile . 'unknown');
    }

    /**
     * @return void
     */
    public function testCheckSettingOfEncodingInIstance()
    {
        $this->assertStringContainsString('This is a File', file_get_contents($this->testFile));
        try {
            $filter = new FileUpperCase('ISO-8859-1');
            $filter($this->testFile);
            $this->assertStringContainsString('THIS IS A FILE', file_get_contents($this->testFile));
        } catch (ExtensionNotLoadedException $e) {
            $this->assertStringContainsString('mbstring is required', $e->getMessage());
        }
    }

    /**
     * @return void
     */
    public function testCheckSettingOfEncodingWithMethod()
    {
        $this->assertStringContainsString('This is a File', file_get_contents($this->testFile));
        try {
            $filter = new FileUpperCase();
            $filter->setEncoding('ISO-8859-1');
            $filter($this->testFile);
            $this->assertStringContainsString('THIS IS A FILE', file_get_contents($this->testFile));
        } catch (ExtensionNotLoadedException $e) {
            $this->assertStringContainsString('mbstring is required', $e->getMessage());
        }
    }

    public function returnUnfilteredDataProvider()
    {
        return [
            [null],
            [new stdClass()],
            [
                [
                    $this->testFile,
                    'something invalid',
                ],
            ],
        ];
    }

    /**
     * @dataProvider returnUnfilteredDataProvider
     * @return void
     */
    public function testReturnUnfiltered($input)
    {
        $filter = new FileUpperCase();
        $filter->setEncoding('ISO-8859-1');

        $this->assertEquals($input, $filter($input));
    }
}
