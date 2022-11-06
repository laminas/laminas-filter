<?php

declare(strict_types=1);

namespace LaminasTest\Filter\Compress;

use Laminas\Filter\Compress\Bz2 as Bz2Compression;
use Laminas\Filter\Exception;
use PHPUnit\Framework\TestCase;

use function extension_loaded;
use function file_exists;
use function sprintf;
use function sys_get_temp_dir;
use function uniqid;
use function unlink;

class Bz2Test extends TestCase
{
    public string $target;

    public function setUp(): void
    {
        if (! extension_loaded('bz2')) {
            self::markTestSkipped('This adapter needs the bz2 extension');
        }

        $this->target = sprintf('%s/%s.bz2', sys_get_temp_dir(), uniqid('laminasilter'));
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
        $filter = new Bz2Compression();

        $content = $filter->compress('compress me');
        self::assertNotEquals('compress me', $content);

        $content = $filter->decompress($content);
        self::assertSame('compress me', $content);
    }

    /**
     * Setting Options
     *
     * @return void
     */
    public function testBz2GetSetOptions()
    {
        $filter = new Bz2Compression();
        self::assertSame(['blocksize' => 4, 'archive' => null], $filter->getOptions());

        self::assertSame(4, $filter->getOptions('blocksize'));

        self::assertNull($filter->getOptions('nooption'));

        $filter->setOptions(['blocksize' => 6]);
        self::assertSame(6, $filter->getOptions('blocksize'));

        $filter->setOptions(['archive' => 'test.txt']);
        self::assertSame('test.txt', $filter->getOptions('archive'));

        $filter->setOptions(['nooption' => 0]);
        self::assertNull($filter->getOptions('nooption'));
    }

    /**
     * Setting Options through constructor
     *
     * @return void
     */
    public function testBz2GetSetOptionsInConstructor()
    {
        $filter2 = new Bz2Compression(['blocksize' => 8]);
        self::assertSame(['blocksize' => 8, 'archive' => null], $filter2->getOptions());
    }

    /**
     * Setting Blocksize
     *
     * @return void
     */
    public function testBz2GetSetBlocksize()
    {
        $filter = new Bz2Compression();
        self::assertSame(4, $filter->getBlocksize());
        $filter->setBlocksize(6);
        self::assertSame(6, $filter->getOptions('blocksize'));

        $this->expectException(Exception\InvalidArgumentException::class);
        $this->expectExceptionMessage('must be between');
        $filter->setBlocksize(15);
    }

    /**
     * Setting Archive
     *
     * @return void
     */
    public function testBz2GetSetArchive()
    {
        $filter = new Bz2Compression();
        self::assertSame(null, $filter->getArchive());
        $filter->setArchive('Testfile.txt');
        self::assertSame('Testfile.txt', $filter->getArchive());
        self::assertSame('Testfile.txt', $filter->getOptions('archive'));
    }

    /**
     * Setting Archive
     *
     * @return void
     */
    public function testBz2CompressToFile()
    {
        $filter  = new Bz2Compression();
        $archive = $this->target;
        $filter->setArchive($archive);

        $content = $filter->compress('compress me');
        self::assertTrue($content);

        $filter2  = new Bz2Compression();
        $content2 = $filter2->decompress($archive);
        self::assertSame('compress me', $content2);

        $filter3 = new Bz2Compression();
        $filter3->setArchive($archive);
        $content3 = $filter3->decompress(null);
        self::assertSame('compress me', $content3);
    }

    /**
     * testing toString
     *
     * @return void
     */
    public function testBz2ToString()
    {
        $filter = new Bz2Compression();
        self::assertSame('Bz2', $filter->toString());
    }

    /**
     * Basic usage
     *
     * @return void
     */
    public function testBz2DecompressArchive()
    {
        $filter  = new Bz2Compression();
        $archive = $this->target;
        $filter->setArchive($archive);

        $content = $filter->compress('compress me');
        self::assertTrue($content);

        $filter2  = new Bz2Compression();
        $content2 = $filter2->decompress($archive);
        self::assertSame('compress me', $content2);
    }

    public function testBz2DecompressNullValueIsAccepted()
    {
        $filter = new Bz2Compression();
        $result = $filter->decompress(null);

        self::assertEmpty($result);
    }
}
