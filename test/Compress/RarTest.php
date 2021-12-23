<?php

declare(strict_types=1);

namespace LaminasTest\Filter\Compress;

use Laminas\Filter\Compress\Rar as RarCompression;
use Laminas\Filter\Exception;
use PHPUnit\Framework\TestCase;

use function dirname;
use function extension_loaded;
use function file_exists;
use function file_get_contents;
use function file_put_contents;
use function is_dir;
use function mkdir;
use function rmdir;
use function sys_get_temp_dir;
use function uniqid;
use function unlink;

use const DIRECTORY_SEPARATOR;

class RarTest extends TestCase
{
    public $tmp;

    public function setUp(): void
    {
        if (! extension_loaded('rar')) {
            $this->markTestSkipped('This adapter needs the rar extension');
        }

        $this->tmp = sys_get_temp_dir() . DIRECTORY_SEPARATOR . uniqid('laminasilter');
        mkdir($this->tmp);

        $files = [
            $this->tmp . '/zipextracted.txt',
            $this->tmp . '/_compress/Compress/First/Second/zipextracted.txt',
            $this->tmp . '/_compress/Compress/First/Second',
            $this->tmp . '/_compress/Compress/First/zipextracted.txt',
            $this->tmp . '/_compress/Compress/First',
            $this->tmp . '/_compress/Compress/zipextracted.txt',
            $this->tmp . '/_compress/Compress',
            $this->tmp . '/_compress/zipextracted.txt',
            $this->tmp . '/_compress',
        ];

        foreach ($files as $file) {
            if (file_exists($file)) {
                if (is_dir($file)) {
                    rmdir($file);
                } else {
                    unlink($file);
                }
            }
        }
    }

    public function tearDown(): void
    {
        $files = [
            $this->tmp . '/zipextracted.txt',
            $this->tmp . '/_compress/Compress/First/Second/zipextracted.txt',
            $this->tmp . '/_compress/Compress/First/Second',
            $this->tmp . '/_compress/Compress/First/zipextracted.txt',
            $this->tmp . '/_compress/Compress/First',
            $this->tmp . '/_compress/Compress/zipextracted.txt',
            $this->tmp . '/_compress/Compress',
            $this->tmp . '/_compress/zipextracted.txt',
            $this->tmp . '/_compress',
        ];

        foreach ($files as $file) {
            if (file_exists($file)) {
                if (is_dir($file)) {
                    rmdir($file);
                } else {
                    unlink($file);
                }
            }
        }
    }

    /**
     * Basic usage
     */
    public function testBasicUsage(): void
    {
        $filter = new RarCompression(
            [
                'archive'  => dirname(__DIR__) . '/_files/compressed.rar',
                'target'   => $this->tmp . '/zipextracted.txt',
                'callback' => [self::class, 'rarCompress'],
            ]
        );

        $content = $filter->compress('compress me');
        $this->assertSame(
            dirname(__DIR__) . DIRECTORY_SEPARATOR . '_files' . DIRECTORY_SEPARATOR . 'compressed.rar',
            $content
        );

        $content = $filter->decompress($content);
        $this->assertTrue($content);
        $content = file_get_contents($this->tmp . '/zipextracted.txt');
        $this->assertSame('compress me', $content);
    }

    /**
     * Setting Options
     */
    public function testRarGetSetOptions(): void
    {
        $filter = new RarCompression();
        $this->assertSame(
            [
                'archive'  => null,
                'callback' => null,
                'password' => null,
                'target'   => '.',
            ],
            $filter->getOptions()
        );

        $this->assertSame(null, $filter->getOptions('archive'));

        $this->assertNull($filter->getOptions('nooption'));
        $filter->setOptions(['nooption' => 'foo']);
        $this->assertNull($filter->getOptions('nooption'));

        $filter->setOptions(['archive' => 'temp.txt']);
        $this->assertSame('temp.txt', $filter->getOptions('archive'));
    }

    /**
     * Setting Archive
     */
    public function testRarGetSetArchive(): void
    {
        $filter = new RarCompression();
        $this->assertSame(null, $filter->getArchive());
        $filter->setArchive('Testfile.txt');
        $this->assertSame('Testfile.txt', $filter->getArchive());
        $this->assertSame('Testfile.txt', $filter->getOptions('archive'));
    }

    /**
     * Setting Password
     */
    public function testRarGetSetPassword(): void
    {
        $filter = new RarCompression();
        $this->assertSame(null, $filter->getPassword());
        $filter->setPassword('test');
        $this->assertSame('test', $filter->getPassword());
        $this->assertSame('test', $filter->getOptions('password'));
        $filter->setOptions(['password' => 'test2']);
        $this->assertSame('test2', $filter->getPassword());
        $this->assertSame('test2', $filter->getOptions('password'));
    }

    /**
     * Setting Target
     */
    public function testRarGetSetTarget(): void
    {
        $filter = new RarCompression();
        $this->assertSame('.', $filter->getTarget());
        $filter->setTarget('Testfile.txt');
        $this->assertSame('Testfile.txt', $filter->getTarget());
        $this->assertSame('Testfile.txt', $filter->getOptions('target'));

        $this->expectException(Exception\InvalidArgumentException::class);
        $this->expectExceptionMessage('does not exist');
        $filter->setTarget('/unknown/path/to/file.txt');
    }

    /**
     * Setting Callback
     */
    public function testSettingCallback(): void
    {
        $filter = new RarCompression();

        $callback = [self::class, 'rarCompress'];
        $filter->setCallback($callback);
        $this->assertSame($callback, $filter->getCallback());
    }

    public function testSettingCallbackThrowsExceptionOnMissingCallback(): void
    {
        $filter = new RarCompression();

        $this->expectException(Exception\RuntimeException::class);
        $this->expectExceptionMessage('No compression callback available');
        $filter->compress('test.txt');
    }

    public function testSettingCallbackThrowsExceptionOnInvalidCallback(): void
    {
        $filter = new RarCompression();

        $this->expectException(Exception\InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid callback provided');
        $filter->setCallback('invalidCallback');
    }

    /**
     * Compress to Archive
     */
    public function testRarCompressFile(): void
    {
        $filter = new RarCompression(
            [
                'archive'  => dirname(__DIR__) . '/_files/compressed.rar',
                'target'   => $this->tmp . '/zipextracted.txt',
                'callback' => [self::class, 'rarCompress'],
            ]
        );
        file_put_contents($this->tmp . '/zipextracted.txt', 'compress me');

        $content = $filter->compress($this->tmp . '/zipextracted.txt');
        $this->assertSame(
            dirname(__DIR__) . DIRECTORY_SEPARATOR . '_files' . DIRECTORY_SEPARATOR . 'compressed.rar',
            $content
        );

        $content = $filter->decompress($content);
        $this->assertTrue($content);
        $content = file_get_contents($this->tmp . '/zipextracted.txt');
        $this->assertSame('compress me', $content);
    }

    /**
     * Compress directory to Filename
     */
    public function testRarCompressDirectory(): void
    {
        $filter  = new RarCompression(
            [
                'archive'  => dirname(__DIR__) . '/_files/compressed.rar',
                'target'   => $this->tmp . '/_compress',
                'callback' => [self::class, 'rarCompress'],
            ]
        );
        $content = $filter->compress(dirname(__DIR__) . '/_files/Compress');
        $this->assertSame(
            dirname(__DIR__) . DIRECTORY_SEPARATOR . '_files' . DIRECTORY_SEPARATOR . 'compressed.rar',
            $content
        );

        mkdir($this->tmp . '/_compress');
        $content = $filter->decompress($content);
        $this->assertTrue($content);

        $base = $this->tmp
            . DIRECTORY_SEPARATOR
            . '_compress'
            . DIRECTORY_SEPARATOR
            . 'Compress'
            . DIRECTORY_SEPARATOR;
        $this->assertFileExists($base);
        $this->assertFileExists($base . 'zipextracted.txt');
        $this->assertFileExists($base . 'First' . DIRECTORY_SEPARATOR . 'zipextracted.txt');
        $this->assertFileExists(
            $base . 'First' . DIRECTORY_SEPARATOR . 'Second' . DIRECTORY_SEPARATOR . 'zipextracted.txt'
        );
    }

    /**
     * testing toString
     */
    public function testRarToString(): void
    {
        $filter = new RarCompression();
        $this->assertSame('Rar', $filter->toString());
    }

    /**
     * Test callback for compression
     *
     * @return unknown
     */
    public static function rarCompress()
    {
        return true;
    }
}
