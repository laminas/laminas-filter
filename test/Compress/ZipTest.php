<?php

declare(strict_types=1);

namespace LaminasTest\Filter\Compress;

use Laminas\Filter\Compress\Zip as ZipCompression;
use Laminas\Filter\Exception;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;

use function extension_loaded;
use function file_exists;
use function file_get_contents;
use function file_put_contents;
use function getenv;
use function is_dir;
use function is_string;
use function mkdir;
use function rmdir;
use function str_replace;
use function sys_get_temp_dir;
use function unlink;

use const DIRECTORY_SEPARATOR;

class ZipTest extends TestCase
{
    private string $tmp;

    public function setUp(): void
    {
        if (! extension_loaded('zip')) {
            self::markTestSkipped('This adapter needs the zip extension');
        }

        $this->tmp = sys_get_temp_dir() . DIRECTORY_SEPARATOR . str_replace('\\', '_', self::class);

        $files = [
            $this->tmp . '/compressed.zip',
            $this->tmp . '/zipextracted.txt',
            $this->tmp . '/zip.tmp',
            $this->tmp . '/_files/_compress/Compress/First/Second/zipextracted.txt',
            $this->tmp . '/_files/_compress/Compress/First/Second',
            $this->tmp . '/_files/_compress/Compress/First/zipextracted.txt',
            $this->tmp . '/_files/_compress/Compress/First',
            $this->tmp . '/_files/_compress/Compress/zipextracted.txt',
            $this->tmp . '/_files/_compress/Compress',
            $this->tmp . '/_files/_compress/zipextracted.txt',
            $this->tmp . '/_files/_compress',
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

        if (! file_exists($this->tmp . '/Compress/First/Second')) {
            mkdir($this->tmp . '/Compress/First/Second', 0777, true);
            file_put_contents($this->tmp . '/Compress/First/Second/zipextracted.txt', 'compress me');
            file_put_contents($this->tmp . '/Compress/First/zipextracted.txt', 'compress me');
            file_put_contents($this->tmp . '/Compress/zipextracted.txt', 'compress me');
        }
    }

    public function tearDown(): void
    {
        $files = [
            $this->tmp . '/compressed.zip',
            $this->tmp . '/zipextracted.txt',
            $this->tmp . '/zip.tmp',
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

        if (! file_exists($this->tmp . '/Compress/First/Second')) {
            mkdir($this->tmp . '/Compress/First/Second', 0777, true);
            file_put_contents($this->tmp . '/Compress/First/Second/zipextracted.txt', 'compress me');
            file_put_contents($this->tmp . '/Compress/First/zipextracted.txt', 'compress me');
            file_put_contents($this->tmp . '/Compress/zipextracted.txt', 'compress me');
        }
    }

    /**
     * Basic usage
     */
    public function testBasicUsage(): void
    {
        if (! $this->zipEnabled()) {
            self::markTestSkipped('ZIP compression tests are currently disabled');
        }

        $filter = new ZipCompression(
            [
                'archive' => $this->tmp . '/compressed.zip',
                'target'  => $this->tmp . '/zipextracted.txt',
            ]
        );

        $content = $filter->compress('compress me');
        self::assertSame($this->tmp . DIRECTORY_SEPARATOR . 'compressed.zip', $content);

        $content = $filter->decompress($content);
        self::assertSame($this->tmp . DIRECTORY_SEPARATOR, $content);
        $content = file_get_contents($this->tmp . '/zipextracted.txt');
        self::assertSame('compress me', $content);
    }

    /**
     * Setting Options
     */
    public function testZipGetSetOptions(): void
    {
        $filter = new ZipCompression();
        self::assertSame(['archive' => null, 'target' => null], $filter->getOptions());

        self::assertSame(null, $filter->getOptions('archive'));

        self::assertNull($filter->getOptions('nooption'));
        $filter->setOptions(['nooption' => 'foo']);
        self::assertNull($filter->getOptions('nooption'));

        $filter->setOptions(['archive' => 'temp.txt']);
        self::assertSame('temp.txt', $filter->getOptions('archive'));
    }

    /**
     * Setting Archive
     */
    public function testZipGetSetArchive(): void
    {
        $filter = new ZipCompression();
        self::assertSame(null, $filter->getArchive());
        $filter->setArchive('Testfile.txt');
        self::assertSame('Testfile.txt', $filter->getArchive());
        self::assertSame('Testfile.txt', $filter->getOptions('archive'));
    }

    /**
     * Setting Target
     */
    public function testZipGetSetTarget(): void
    {
        $filter = new ZipCompression();
        self::assertNull($filter->getTarget());
        $filter->setTarget('Testfile.txt');
        self::assertSame('Testfile.txt', $filter->getTarget());
        self::assertSame('Testfile.txt', $filter->getOptions('target'));

        $this->expectException(Exception\InvalidArgumentException::class);
        $this->expectExceptionMessage('does not exist');
        $filter->setTarget('/unknown/path/to/file.txt');
    }

    /**
     * Compress to Archive
     */
    public function testZipCompressFile(): void
    {
        if (! $this->zipEnabled()) {
            self::markTestSkipped('ZIP compression tests are currently disabled');
        }

        $filter = new ZipCompression(
            [
                'archive' => $this->tmp . '/compressed.zip',
                'target'  => $this->tmp . '/zipextracted.txt',
            ]
        );
        file_put_contents($this->tmp . '/zipextracted.txt', 'compress me');

        $content = $filter->compress($this->tmp . '/zipextracted.txt');
        self::assertSame($this->tmp . DIRECTORY_SEPARATOR . 'compressed.zip', $content);

        $content = $filter->decompress($content);
        self::assertSame($this->tmp . DIRECTORY_SEPARATOR, $content);
        $content = file_get_contents($this->tmp . '/zipextracted.txt');
        self::assertSame('compress me', $content);
    }

    /**
     * Basic usage
     */
    public function testCompressNonExistingTargetFile(): void
    {
        if (! $this->zipEnabled()) {
            self::markTestSkipped('ZIP compression tests are currently disabled');
        }

        $filter = new ZipCompression(
            [
                'archive' => $this->tmp . '/compressed.zip',
                'target'  => $this->tmp,
            ]
        );

        $content = $filter->compress('compress me');
        self::assertSame($this->tmp . DIRECTORY_SEPARATOR . 'compressed.zip', $content);

        $content = $filter->decompress($content);
        self::assertSame($this->tmp . DIRECTORY_SEPARATOR, $content);
        $content = file_get_contents($this->tmp . '/zip.tmp');
        self::assertSame('compress me', $content);
    }

    /**
     * Compress directory to Archive
     */
    public function testZipCompressDirectory(): void
    {
        if (! $this->zipEnabled()) {
            self::markTestSkipped('ZIP compression tests are currently disabled');
        }

        $filter  = new ZipCompression(
            [
                'archive' => $this->tmp . '/compressed.zip',
                'target'  => $this->tmp . '/_compress',
            ]
        );
        $content = $filter->compress($this->tmp . '/Compress');
        self::assertSame($this->tmp . DIRECTORY_SEPARATOR . 'compressed.zip', $content);

        mkdir($this->tmp . DIRECTORY_SEPARATOR . '_compress');
        $content = $filter->decompress($content);
        self::assertSame($this->tmp . DIRECTORY_SEPARATOR . '_compress'
                            . DIRECTORY_SEPARATOR, $content);

        $base = $this->tmp . DIRECTORY_SEPARATOR . '_compress' . DIRECTORY_SEPARATOR . 'Compress' . DIRECTORY_SEPARATOR;
        self::assertFileExists($base);
        self::assertFileExists($base . 'zipextracted.txt');
        self::assertFileExists($base . 'First' . DIRECTORY_SEPARATOR . 'zipextracted.txt');
        self::assertFileExists($base . 'First' . DIRECTORY_SEPARATOR
                          . 'Second' . DIRECTORY_SEPARATOR . 'zipextracted.txt');
        $content = file_get_contents($this->tmp . '/Compress/zipextracted.txt');
        self::assertSame('compress me', $content);
    }

    /**
     * testing toString
     */
    public function testZipToString(): void
    {
        $filter = new ZipCompression();
        self::assertSame('Zip', $filter->toString());
    }

    public function testDecompressWillThrowExceptionWhenDecompressingWithNoTarget(): void
    {
        if (! $this->zipEnabled()) {
            self::markTestSkipped('ZIP compression tests are currently disabled');
        }

        $filter = new ZipCompression(
            [
                'archive' => $this->tmp . '/compressed.zip',
                'target'  => $this->tmp . '/_compress',
            ]
        );

        $content = $filter->compress('compress me');
        self::assertSame($this->tmp . DIRECTORY_SEPARATOR . 'compressed.zip', $content);

        $filter  = new ZipCompression(
            [
                'archive' => $this->tmp . '/compressed.zip',
                'target'  => $this->tmp . '/_compress',
            ]
        );
        $content = $filter->decompress($content);
        self::assertSame($this->tmp . DIRECTORY_SEPARATOR, $content);
        $content = file_get_contents($this->tmp . '/_compress');
        self::assertSame('compress me', $content);
    }

    #[Group('6026')]
    public function testDecompressWhenNoArchieveInClass(): void
    {
        if (! $this->zipEnabled()) {
            self::markTestSkipped('ZIP compression tests are currently disabled');
        }

        $filter = new ZipCompression(
            [
                'archive' => $this->tmp . '/compressed.zip',
                'target'  => $this->tmp . '/_compress',
            ]
        );

        $content = $filter->compress('compress me');
        self::assertSame($this->tmp . DIRECTORY_SEPARATOR . 'compressed.zip', $content);

        $filter  = new ZipCompression(
            [
                'target' => $this->tmp . '/_compress',
            ]
        );
        $content = $filter->decompress($content);
        self::assertSame($this->tmp . DIRECTORY_SEPARATOR, $content);
        $content = file_get_contents($this->tmp . '/_compress');
        self::assertSame('compress me', $content);
    }

    private function zipEnabled(): bool
    {
        /**
         * PHPUnit casts true|false env vars to "1"|""
         */
        $value = getenv('TESTS_LAMINAS_FILTER_COMPRESS_ZIP_ENABLED');

        return is_string($value) && (int) $value === 1;
    }
}
