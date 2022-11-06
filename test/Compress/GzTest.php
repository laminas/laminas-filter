<?php

declare(strict_types=1);

namespace LaminasTest\Filter\Compress;

use Laminas\Filter\Compress\Gz as GzCompression;
use Laminas\Filter\Exception;
use PHPUnit\Framework\TestCase;

use function extension_loaded;
use function file_exists;
use function sprintf;
use function sys_get_temp_dir;
use function uniqid;
use function unlink;

class GzTest extends TestCase
{
    public string $target;

    public function setUp(): void
    {
        if (! extension_loaded('zlib')) {
            self::markTestSkipped('This adapter needs the zlib extension');
        }

        $this->target = sprintf('%s/%s.gz', sys_get_temp_dir(), uniqid('laminasilter'));
    }

    public function tearDown(): void
    {
        if (file_exists($this->target)) {
            unlink($this->target);
        }
    }

    /**
     * Basic usage
     */
    public function testBasicUsage(): void
    {
        $filter = new GzCompression();

        $content = $filter->compress('compress me');
        self::assertNotEquals('compress me', $content);

        $content = $filter->decompress($content);
        self::assertSame('compress me', $content);
    }

    /**
     * Setting Options
     */
    public function testGzGetSetOptions(): void
    {
        $filter = new GzCompression();
        self::assertSame(['level' => 9, 'mode' => 'compress', 'archive' => null], $filter->getOptions());

        self::assertSame(9, $filter->getOptions('level'));

        self::assertNull($filter->getOptions('nooption'));
        $filter->setOptions(['nooption' => 'foo']);
        self::assertNull($filter->getOptions('nooption'));

        $filter->setOptions(['level' => 6]);
        self::assertSame(6, $filter->getOptions('level'));

        $filter->setOptions(['mode' => 'deflate']);
        self::assertSame('deflate', $filter->getOptions('mode'));

        $filter->setOptions(['archive' => 'test.txt']);
        self::assertSame('test.txt', $filter->getOptions('archive'));
    }

    /**
     * Setting Options through constructor
     */
    public function testGzGetSetOptionsInConstructor(): void
    {
        $filter2 = new GzCompression(['level' => 8]);
        self::assertSame(['level' => 8, 'mode' => 'compress', 'archive' => null], $filter2->getOptions());
    }

    /**
     * Setting Level
     */
    public function testGzGetSetLevel(): void
    {
        $filter = new GzCompression();
        self::assertSame(9, $filter->getLevel());
        $filter->setLevel(6);
        self::assertSame(6, $filter->getOptions('level'));

        $this->expectException(Exception\InvalidArgumentException::class);
        $this->expectExceptionMessage('must be between');
        $filter->setLevel(15);
    }

    /**
     * Setting Mode
     */
    public function testGzGetSetMode(): void
    {
        $filter = new GzCompression();
        self::assertSame('compress', $filter->getMode());
        $filter->setMode('deflate');
        self::assertSame('deflate', $filter->getOptions('mode'));

        $this->expectException(Exception\InvalidArgumentException::class);
        $this->expectExceptionMessage('mode not supported');
        $filter->setMode('unknown');
    }

    /**
     * Setting Archive
     */
    public function testGzGetSetArchive(): void
    {
        $filter = new GzCompression();
        self::assertSame(null, $filter->getArchive());
        $filter->setArchive('Testfile.txt');
        self::assertSame('Testfile.txt', $filter->getArchive());
        self::assertSame('Testfile.txt', $filter->getOptions('archive'));
    }

    /**
     * Setting Archive
     */
    public function testGzCompressToFile(): void
    {
        $filter  = new GzCompression();
        $archive = $this->target;
        $filter->setArchive($archive);

        $content = $filter->compress('compress me');
        self::assertTrue($content);

        $filter2  = new GzCompression();
        $content2 = $filter2->decompress($archive);
        self::assertSame('compress me', $content2);

        $filter3 = new GzCompression();
        $filter3->setArchive($archive);
        $content3 = $filter3->decompress(null);
        self::assertSame('compress me', $content3);
    }

    /**
     * Test deflate
     */
    public function testGzDeflate(): void
    {
        $filter = new GzCompression(['mode' => 'deflate']);

        $content = $filter->compress('compress me');
        self::assertNotEquals('compress me', $content);

        $content = $filter->decompress($content);
        self::assertSame('compress me', $content);
    }

    /**
     * testing toString
     */
    public function testGzToString(): void
    {
        $filter = new GzCompression();
        self::assertSame('Gz', $filter->toString());
    }

    public function testGzDecompressNullThrowsRuntimeException(): void
    {
        $this->expectException(Exception\RuntimeException::class);
        $this->expectExceptionMessage('Error during decompression');

        $filter = new GzCompression();
        $filter->decompress(null);
    }
}
