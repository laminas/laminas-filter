<?php

declare(strict_types=1);

namespace LaminasTest\Filter;

use Laminas\Filter\Decompress as DecompressFilter;
use PHPUnit\Framework\TestCase;
use stdClass;

use function extension_loaded;
use function file_exists;
use function is_dir;
use function mkdir;
use function rmdir;
use function sprintf;
use function sys_get_temp_dir;
use function uniqid;
use function unlink;

class DecompressTest extends TestCase
{
    public $tmpDir;

    public function setUp(): void
    {
        if (! extension_loaded('bz2')) {
            $this->markTestSkipped('This filter is tested with the bz2 extension');
        }

        $this->tmpDir = sprintf('%s/%s', sys_get_temp_dir(), uniqid('laminasilter'));
        mkdir($this->tmpDir, 0775, true);
    }

    public function tearDown(): void
    {
        if (is_dir($this->tmpDir)) {
            if (file_exists($this->tmpDir . '/compressed.bz2')) {
                unlink($this->tmpDir . '/compressed.bz2');
            }
            rmdir($this->tmpDir);
        }
    }

    /**
     * Basic usage
     *
     * @return void
     */
    public function testBasicUsage()
    {
        $filter = new DecompressFilter('bz2');

        $text       = 'compress me';
        $compressed = $filter->compress($text);
        $this->assertNotEquals($text, $compressed);

        $decompressed = $filter($compressed);
        $this->assertEquals($text, $decompressed);
    }

    /**
     * Setting Archive
     *
     * @return void
     */
    public function testCompressToFile()
    {
        $filter  = new DecompressFilter('bz2');
        $archive = $this->tmpDir . '/compressed.bz2';
        $filter->setArchive($archive);

        $content = $filter->compress('compress me');
        $this->assertTrue($content);

        $filter2  = new DecompressFilter('bz2');
        $content2 = $filter2($archive);
        $this->assertEquals('compress me', $content2);

        $filter3 = new DecompressFilter('bz2');
        $filter3->setArchive($archive);
        $content3 = $filter3(null);
        $this->assertEquals('compress me', $content3);
    }

    /**
     * Basic usage
     *
     * @return void
     */
    public function testDecompressArchive()
    {
        $filter  = new DecompressFilter('bz2');
        $archive = $this->tmpDir . '/compressed.bz2';
        $filter->setArchive($archive);

        $content = $filter->compress('compress me');
        $this->assertTrue($content);

        $filter2  = new DecompressFilter('bz2');
        $content2 = $filter2($archive);
        $this->assertEquals('compress me', $content2);
    }

    public function testFilterMethodProxiesToDecompress()
    {
        $filter  = new DecompressFilter('bz2');
        $archive = $this->tmpDir . '/compressed.bz2';
        $filter->setArchive($archive);

        $content = $filter->compress('compress me');
        $this->assertTrue($content);

        $filter2  = new DecompressFilter('bz2');
        $content2 = $filter2->filter($archive);
        $this->assertEquals('compress me', $content2);
    }

    public function returnUnfilteredDataProvider()
    {
        return [
            [null],
            [new stdClass()],
            [
                [
                    'decompress me',
                    'decompress me too, please',
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
        $filter = new DecompressFilter('bz2');

        $this->assertEquals($input, $filter($input));
    }
}
