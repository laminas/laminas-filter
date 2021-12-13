<?php

declare(strict_types=1);

namespace LaminasTest\Filter;

use Laminas\Filter\Boolean;
use Laminas\Filter\Compress as CompressFilter;
use Laminas\Filter\Compress\CompressionAlgorithmInterface;
use Laminas\Filter\Exception;
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

class CompressTest extends TestCase
{
    public $tmpDir;

    public function setUp(): void
    {
        if (! extension_loaded('bz2') && ! extension_loaded('zlib')) {
            $this->markTestSkipped('This filter requires bz2 of zlib extension');
        }

        $this->tmpDir = sprintf('%s/%s', sys_get_temp_dir(), uniqid('laminasfilter'));
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
     */
    public function testBasicUsage($filterType): void
    {
        $filter = new CompressFilter($filterType);

        $text       = 'compress me';
        $compressed = $filter($text);
        $this->assertNotEquals($text, $compressed);

        $decompressed = $filter->decompress($compressed);
        $this->assertSame($text, $decompressed);
    }

    /**
     * Setting Options
     *
     * @dataProvider returnFilterType
     */
    public function testGetSetAdapterOptionsInConstructor($filterType): void
    {
        $filter = new CompressFilter([
            'adapter' => $filterType,
            'options' => [
                'archive' => 'test.txt',
            ],
        ]);

        $this->assertSame(
            ['archive' => 'test.txt'],
            $filter->getAdapterOptions()
        );

        $adapter = $filter->getAdapter();
        $this->assertSame('test.txt', $adapter->getArchive());
    }

    /**
     * Setting Options through constructor
     *
     * @dataProvider returnFilterType
     */
    public function testGetSetAdapterOptions($filterType): void
    {
        $filter = new CompressFilter($filterType);
        $filter->setAdapterOptions([
            'archive' => 'test.txt',
        ]);
        $this->assertSame(
            ['archive' => 'test.txt'],
            $filter->getAdapterOptions()
        );
        $adapter = $filter->getAdapter();
        $this->assertSame('test.txt', $adapter->getArchive());
    }

    /**
     * Setting Blocksize (works only for bz2)
     */
    public function testGetSetBlocksize(): void
    {
        if (! extension_loaded('bz2')) {
            $this->markTestSkipped('Extension bz2 is required for this test');
        }

        $filter = new CompressFilter('bz2');
        $this->assertSame(4, $filter->getBlocksize());
        $filter->setBlocksize(6);
        $this->assertSame(6, $filter->getOptions('blocksize'));

        $this->expectException(Exception\InvalidArgumentException::class);
        $this->expectExceptionMessage('must be between');
        $filter->setBlocksize(15);
    }

    /**
     * Setting Archive
     *
     * @dataProvider returnFilterType
     */
    public function testGetSetArchive($filterType): void
    {
        $filter = new CompressFilter($filterType);
        $this->assertSame(null, $filter->getArchive());
        $filter->setArchive('Testfile.txt');
        $this->assertSame('Testfile.txt', $filter->getArchive());
        $this->assertSame('Testfile.txt', $filter->getOptions('archive'));
    }

    /**
     * Setting Archive
     *
     * @dataProvider returnFilterType
     */
    public function testCompressToFile($filterType): void
    {
        $filter  = new CompressFilter($filterType);
        $archive = $this->tmpDir . '/compressed.' . $filterType;
        $filter->setArchive($archive);

        $content = $filter('compress me');
        $this->assertTrue($content);

        $filter2  = new CompressFilter($filterType);
        $content2 = $filter2->decompress($archive);
        $this->assertSame('compress me', $content2);

        $filter3 = new CompressFilter($filterType);
        $filter3->setArchive($archive);
        $content3 = $filter3->decompress(null);
        $this->assertSame('compress me', $content3);
    }

    /**
     * testing toString
     *
     * @dataProvider returnFilterType
     */
    public function testToString($filterType): void
    {
        $filter = new CompressFilter($filterType);
        $this->assertEqualsIgnoringCase($filterType, $filter->toString());
    }

    /**
     * testing getAdapter
     *
     * @dataProvider returnFilterType
     */
    public function testGetAdapter($filterType): void
    {
        $filter  = new CompressFilter($filterType);
        $adapter = $filter->getAdapter();
        $this->assertInstanceOf(CompressionAlgorithmInterface::class, $adapter);
        $this->assertEqualsIgnoringCase($filterType, $filter->getAdapterName());
    }

    /**
     * Setting Adapter
     */
    public function testSetAdapter(): void
    {
        if (! extension_loaded('zlib')) {
            $this->markTestSkipped('This filter is tested with the zlib extension');
        }

        $filter = new CompressFilter();
        $this->assertSame('Gz', $filter->getAdapterName());

        $filter->setAdapter(Boolean::class);

        $this->expectException(Exception\InvalidArgumentException::class);
        $this->expectExceptionMessage('does not implement');
        $adapter = $filter->getAdapter();
    }

    /**
     * Decompress archiv
     *
     * @dataProvider returnFilterType
     */
    public function testDecompressArchive($filterType): void
    {
        $filter  = new CompressFilter($filterType);
        $archive = $this->tmpDir . '/compressed.' . $filterType;
        $filter->setArchive($archive);

        $content = $filter('compress me');
        $this->assertTrue($content);

        $filter2  = new CompressFilter($filterType);
        $content2 = $filter2->decompress($archive);
        $this->assertSame('compress me', $content2);
    }

    /**
     * Setting invalid method
     */
    public function testInvalidMethod(): void
    {
        $filter = new CompressFilter();

        $this->expectException(Exception\BadMethodCallException::class);
        $this->expectExceptionMessage('Unknown method');
        $filter->invalidMethod();
    }

    public function returnUnfilteredDataProvider(): iterable
    {
        foreach ($this->returnFilterType() as $parameters) {
            yield [$parameters[0], null];
            yield [$parameters[0], new stdClass()];
            yield [
                $parameters[0],
                [
                    'compress me',
                    'compress me too, please',
                ],
            ];
        }
    }

    /**
     * @dataProvider returnUnfilteredDataProvider
     */
    public function testReturnUnfiltered($filterType, $input): void
    {
        $filter = new CompressFilter($filterType);

        $this->assertSame($input, $filter($input));
    }
}
