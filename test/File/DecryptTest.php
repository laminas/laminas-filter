<?php

declare(strict_types=1);

namespace LaminasTest\Filter\File;

use Laminas\Filter\Exception;
use Laminas\Filter\File\Decrypt as FileDecrypt;
use Laminas\Filter\File\Encrypt as FileEncrypt;
use PHPUnit\Framework\TestCase;
use stdClass;

use function dirname;
use function extension_loaded;
use function file_exists;
use function file_get_contents;
use function is_dir;
use function mkdir;
use function rmdir;
use function sprintf;
use function sys_get_temp_dir;
use function trim;
use function uniqid;
use function unlink;

class DecryptTest extends TestCase
{
    public $fileToEncrypt;

    public $tmpDir;

    public function setUp(): void
    {
        if (! extension_loaded('mcrypt') && ! extension_loaded('openssl')) {
            $this->markTestSkipped('This filter needs the mcrypt or openssl extension');
        }

        $this->tmpDir = sprintf('%s/%s', sys_get_temp_dir(), uniqid('laminasilter'));
        mkdir($this->tmpDir, 0775, true);

        $this->fileToEncrypt = dirname(__DIR__) . '/_files/encryption.txt';
    }

    public function tearDown(): void
    {
        if (is_dir($this->tmpDir)) {
            if (file_exists($this->tmpDir . '/newencryption.txt')) {
                unlink($this->tmpDir . '/newencryption.txt');
            }

            if (file_exists($this->tmpDir . '/newencryption2.txt')) {
                unlink($this->tmpDir . '/newencryption2.txt');
            }

            rmdir($this->tmpDir);
        }
    }

    /**
     * Ensures that the filter follows expected behavior
     *
     * @return void
     */
    public function testBasic()
    {
        $filter = new FileEncrypt();
        $filter->setFilename($this->tmpDir . '/newencryption.txt');

        $this->assertEquals(
            $this->tmpDir . '/newencryption.txt',
            $filter->getFilename()
        );

        $filter->setKey('1234567890123456');
        $filter->filter($this->fileToEncrypt);

        $filter = new FileDecrypt();

        $this->assertNotEquals(
            'Encryption',
            file_get_contents($this->tmpDir . '/newencryption.txt')
        );

        $filter->setKey('1234567890123456');
        $this->assertEquals(
            $this->tmpDir . '/newencryption.txt',
            $filter->filter($this->tmpDir . '/newencryption.txt')
        );

        $this->assertEquals(
            'Encryption',
            trim(file_get_contents($this->tmpDir . '/newencryption.txt'))
        );
    }

    public function testEncryptionWithDecryption()
    {
        $filter = new FileEncrypt();
        $filter->setFilename($this->tmpDir . '/newencryption.txt');
        $filter->setKey('1234567890123456');
        $this->assertEquals(
            $this->tmpDir . '/newencryption.txt',
            $filter->filter($this->fileToEncrypt)
        );

        $this->assertNotEquals(
            'Encryption',
            file_get_contents($this->tmpDir . '/newencryption.txt')
        );

        $filter = new FileDecrypt();
        $filter->setFilename($this->tmpDir . '/newencryption2.txt');

        $this->assertEquals(
            $this->tmpDir . '/newencryption2.txt',
            $filter->getFilename()
        );

        $filter->setKey('1234567890123456');
        $input = $filter->filter($this->tmpDir . '/newencryption.txt');
        $this->assertEquals($this->tmpDir . '/newencryption2.txt', $input);

        $this->assertEquals(
            'Encryption',
            trim(file_get_contents($this->tmpDir . '/newencryption2.txt'))
        );
    }

    /**
     * @return void
     */
    public function testNonExistingFile()
    {
        $filter = new FileDecrypt();
        $filter->setVector('1234567890123456');

        $this->expectException(Exception\InvalidArgumentException::class);
        $this->expectExceptionMessage('not found');
        $filter->filter($this->tmpDir . '/nofile.txt');
    }

    public function returnUnfilteredDataProvider()
    {
        return [
            [null],
            [new stdClass()],
            [
                [
                    $this->tmpDir . '/nofile.txt',
                    $this->tmpDir . '/nofile2.txt',
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
        $filter = new FileDecrypt();
        $filter->setKey('1234567890123456');

        $this->assertEquals($input, $filter($input));
    }
}
