<?php

declare(strict_types=1);

namespace LaminasTest\Filter\Compress;

use Laminas\Filter\Compress\Tar as TarCompression;
use Laminas\Filter\Exception;
use PHPUnit\Framework\TestCase;

use function dirname;
use function file_exists;
use function file_get_contents;
use function file_put_contents;
use function implode;
use function is_dir;
use function microtime;
use function mkdir;
use function rmdir;
use function sprintf;
use function strtolower;
use function sys_get_temp_dir;
use function uniqid;
use function unlink;

use const DIRECTORY_SEPARATOR;

class TarTest extends TestCase
{
    public string $tmp;

    public function setUp(): void
    {
        $this->tmp = sprintf('%s/%s', sys_get_temp_dir(), uniqid('laminasilter'));
        mkdir($this->tmp, 0775, true);
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
            $this->tmp . '/compressed.tar',
            $this->tmp . '/compressed.tar.gz',
            $this->tmp . '/compressed.tar.bz2',
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
        $filter = new TarCompression(
            [
                'archive' => $this->tmp . '/compressed.tar',
                'target'  => $this->tmp . '/zipextracted.txt',
            ]
        );

        $content = $filter->compress('compress me');
        self::assertSame(
            $this->tmp . DIRECTORY_SEPARATOR . 'compressed.tar',
            $content
        );

        $content = $filter->decompress($content);
        self::assertSame($this->tmp . DIRECTORY_SEPARATOR, $content);
        $content = file_get_contents($this->tmp . '/zipextracted.txt');
        self::assertSame('compress me', $content);
    }

    /**
     * Setting Options
     */
    public function testTarGetSetOptions(): void
    {
        $filter = new TarCompression();
        self::assertSame(
            [
                'archive' => null,
                'target'  => '.',
                'mode'    => null,
            ],
            $filter->getOptions()
        );

        self::assertSame(null, $filter->getOptions('archive'));

        self::assertNull($filter->getOptions('nooption'));
        $filter->setOptions(['nooptions' => 'foo']);
        self::assertNull($filter->getOptions('nooption'));

        $filter->setOptions(['archive' => 'temp.txt']);
        self::assertSame('temp.txt', $filter->getOptions('archive'));
    }

    /**
     * Setting Archive
     */
    public function testTarGetSetArchive(): void
    {
        $filter = new TarCompression();
        self::assertSame(null, $filter->getArchive());
        $filter->setArchive('Testfile.txt');
        self::assertSame('Testfile.txt', $filter->getArchive());
        self::assertSame('Testfile.txt', $filter->getOptions('archive'));
    }

    /**
     * Setting Target
     */
    public function testTarGetSetTarget(): void
    {
        $filter = new TarCompression();
        self::assertSame('.', $filter->getTarget());
        $filter->setTarget('Testfile.txt');
        self::assertSame('Testfile.txt', $filter->getTarget());
        self::assertSame('Testfile.txt', $filter->getOptions('target'));

        $this->expectException(Exception\InvalidArgumentException::class);
        $this->expectExceptionMessage('does not exist');
        $filter->setTarget('/unknown/path/to/file.txt');
    }

    /**
     * Setting Archive
     */
    public function testTarCompressToFile(): void
    {
        $filter = new TarCompression(
            [
                'archive' => $this->tmp . '/compressed.tar',
                'target'  => $this->tmp . '/zipextracted.txt',
            ]
        );
        file_put_contents($this->tmp . '/zipextracted.txt', 'compress me');

        $content = $filter->compress($this->tmp . '/zipextracted.txt');
        self::assertSame(
            $this->tmp . DIRECTORY_SEPARATOR . 'compressed.tar',
            $content
        );

        $content = $filter->decompress($content);
        self::assertSame($this->tmp . DIRECTORY_SEPARATOR, $content);
        $content = file_get_contents($this->tmp . '/zipextracted.txt');
        self::assertSame('compress me', $content);
    }

    /**
     * Compress directory to Filename
     */
    public function testTarCompressDirectory(): void
    {
        $filter  = new TarCompression(
            [
                'archive' => $this->tmp . '/compressed.tar',
                'target'  => $this->tmp . '/_compress',
            ]
        );
        $content = $filter->compress(dirname(__DIR__) . '/_files/Compress');
        self::assertSame(
            $this->tmp . DIRECTORY_SEPARATOR . 'compressed.tar',
            $content
        );
    }

    public function testSetModeShouldWorkWithCaseInsensitive(): void
    {
        if (! extension_loaded('bz2')) {
            self::markTestSkipped('This adapter needs the bz2 extension');
        }

        $filter = new TarCompression();
        $filter->setTarget($this->tmp . '/zipextracted.txt');

        foreach (['GZ', 'Bz2'] as $mode) {
            $archive = implode(DIRECTORY_SEPARATOR, [
                $this->tmp,
                'compressed.tar.',
            ]) . strtolower($mode);
            $filter->setArchive($archive);
            $filter->setMode($mode);
            $content = $filter->compress('compress me');
            self::assertSame($archive, $content);
        }
    }

    /**
     * testing toString
     */
    public function testTarToString(): void
    {
        $filter = new TarCompression();
        self::assertSame('Tar', $filter->toString());
    }

    /**
     * @see https://github.com/zendframework/zend-filter/issues/41
     */
    public function testDecompressionDoesNotRequireArchive(): void
    {
        $filter = new TarCompression([
            'archive' => $this->tmp . '/compressed.tar',
            'target'  => $this->tmp . '/zipextracted.txt',
        ]);

        $content    = 'compress me ' . microtime(true);
        $compressed = $filter->compress($content);

        self::assertSame($this->tmp . DIRECTORY_SEPARATOR . 'compressed.tar', $compressed);

        $target = $this->tmp;
        $filter = new TarCompression([
            'target' => $target,
        ]);

        $decompressed = $filter->decompress($compressed);
        self::assertSame($target, $decompressed);
        // per documentation, tar includes full path
        $file = $target . DIRECTORY_SEPARATOR . $target . DIRECTORY_SEPARATOR . '/zipextracted.txt';
        self::assertFileExists($file);
        self::assertSame($content, file_get_contents($file));
    }
}
