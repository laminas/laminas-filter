<?php

declare(strict_types=1);

namespace LaminasTest\Filter;

use Laminas\Filter\Decompress as DecompressFilter;
use Laminas\Filter\Exception\RuntimeException;
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
            foreach ($this->returnFilterType() as $parameters) {
                if (file_exists($this->tmpDir . '/compressed.' . $parameters[0])) {
                    unlink($this->tmpDir . '/compressed.' . $parameters[0]);
                }
            }
            rmdir($this->tmpDir);
        }
    }

    public function returnFilterType(): iterable
    {
        if (extension_loaded('bz2')) {
            yield ['bz2'];
        }
        if (extension_loaded('zlib')) {
            yield ['gz'];
        }
    }

    /**
     * Basic usage
     *
     * @dataProvider returnFilterType
     * @return void
     */
    public function testBasicUsage($filterType)
    {
        $filter = new DecompressFilter($filterType);

        $text       = 'compress me';
        $compressed = $filter->compress($text);
        $this->assertNotEquals($text, $compressed);

        $decompressed = $filter($compressed);
        $this->assertEquals($text, $decompressed);
    }

    /**
     * Setting Archive
     *
     * @dataProvider returnFilterType
     * @return void
     */
    public function testCompressToFile($filterType)
    {
        $filter  = new DecompressFilter($filterType);
        $archive = $this->tmpDir . '/compressed.' . $filterType;
        $filter->setArchive($archive);

        $content = $filter->compress('compress me');
        $this->assertTrue($content);

        $filter2  = new DecompressFilter($filterType);
        $content2 = $filter2($archive);
        $this->assertEquals('compress me', $content2);

        $filter3 = new DecompressFilter($filterType);
        $filter3->setArchive($archive);
        $content3 = $filter3(null);
        $this->assertEquals('compress me', $content3);
    }

    /**
     * Basic usage
     *
     * @dataProvider returnFilterType
     * @return void
     */
    public function testDecompressArchive($filterType)
    {
        $filter  = new DecompressFilter($filterType);
        $archive = $this->tmpDir . '/compressed.' . $filterType;
        $filter->setArchive($archive);

        $content = $filter->compress('compress me');
        $this->assertTrue($content);

        $filter2  = new DecompressFilter($filterType);
        $content2 = $filter2($archive);
        $this->assertEquals('compress me', $content2);
    }

    /**
     * @dataProvider returnFilterType
     */
    public function testFilterMethodProxiesToDecompress($filterType)
    {
        $filter  = new DecompressFilter($filterType);
        $archive = $this->tmpDir . '/compressed.' . $filterType;
        $filter->setArchive($archive);

        $content = $filter->compress('compress me');
        $this->assertTrue($content);

        $filter2  = new DecompressFilter($filterType);
        $content2 = $filter2->filter($archive);
        $this->assertEquals('compress me', $content2);
    }

    public function returnUnfilteredDataProvider(): iterable
    {
        foreach ($this->returnFilterType() as $parameter) {
            yield [$parameter[0], new stdClass()];
            yield [
                $parameter[0],
                [
                    'decompress me',
                    'decompress me too, please',
                ],
            ];
        }
    }

    /**
     * @dataProvider returnUnfilteredDataProvider
     * @return void
     */
    public function testReturnUnfiltered($filterType, $input)
    {
        $filter = new DecompressFilter($filterType);

        $this->assertEquals($input, $filter($input));
    }

    /**
     * @dataProvider returnFilterType
     */
    public function testDecompressNullValueThrowsRuntimeException($filterType)
    {
        $this->expectException(RuntimeException::class);
        $this->expectErrorMessage('Error during decompression');
        $filter = new DecompressFilter($filterType);
        $filter->filter(null);
    }
}
