<?php

declare(strict_types=1);

namespace LaminasTest\Filter;

use Laminas\Filter\Boolean;
use Laminas\Filter\Compress as CompressFilter;
use Laminas\Filter\Compress\CompressionAlgorithmInterface;
use Laminas\Filter\Exception;
use PHPUnit\Framework\Attributes\DataProvider;
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
    private string $tmpDir;

    public function setUp(): void
    {
        if (! extension_loaded('bz2') && ! extension_loaded('zlib')) {
            self::markTestSkipped('This filter requires bz2 of zlib extension');
        }

        $this->tmpDir = sprintf('%s/%s', sys_get_temp_dir(), uniqid('laminasfilter'));
        mkdir($this->tmpDir, 0775, true);
    }

    public function tearDown(): void
    {
        if (is_dir($this->tmpDir)) {
            foreach (self::returnFilterType() as $parameters) {
                if (file_exists($this->tmpDir . '/compressed.' . $parameters[0])) {
                    unlink($this->tmpDir . '/compressed.' . $parameters[0]);
                }
            }
            rmdir($this->tmpDir);
        }
    }

    /** @return iterable<array-key, array{0: string}> */
    public static function returnFilterType(): iterable
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
     */
    #[DataProvider('returnFilterType')]
    public function testBasicUsage(string $filterType): void
    {
        $filter = new CompressFilter($filterType);

        $text       = 'compress me';
        $compressed = $filter($text);
        self::assertNotEquals($text, $compressed);

        $decompressed = $filter->decompress($compressed);
        self::assertSame($text, $decompressed);
    }

    /**
     * Setting Options
     */
    #[DataProvider('returnFilterType')]
    public function testGetSetAdapterOptionsInConstructor(string $filterType): void
    {
        $filter = new CompressFilter([
            'adapter' => $filterType,
            'options' => [
                'archive' => 'test.txt',
            ],
        ]);

        self::assertSame(
            ['archive' => 'test.txt'],
            $filter->getAdapterOptions()
        );

        $adapter = $filter->getAdapter();
        self::assertSame('test.txt', $adapter->getArchive());
    }

    /**
     * Setting Options through constructor
     */
    #[DataProvider('returnFilterType')]
    public function testGetSetAdapterOptions(string $filterType): void
    {
        $filter = new CompressFilter($filterType);
        $filter->setAdapterOptions([
            'archive' => 'test.txt',
        ]);
        self::assertSame(
            ['archive' => 'test.txt'],
            $filter->getAdapterOptions()
        );
        $adapter = $filter->getAdapter();
        self::assertSame('test.txt', $adapter->getArchive());
    }

    /**
     * Setting Blocksize (works only for bz2)
     */
    public function testGetSetBlocksize(): void
    {
        if (! extension_loaded('bz2')) {
            self::markTestSkipped('Extension bz2 is required for this test');
        }

        $filter = new CompressFilter('bz2');
        self::assertSame(4, $filter->getBlocksize());
        $filter->setBlocksize(6);
        self::assertSame(6, $filter->getOptions('blocksize'));

        $this->expectException(Exception\InvalidArgumentException::class);
        $this->expectExceptionMessage('must be between');
        $filter->setBlocksize(15);
    }

    /**
     * Setting Archive
     */
    #[DataProvider('returnFilterType')]
    public function testGetSetArchive(string $filterType): void
    {
        $filter = new CompressFilter($filterType);
        self::assertSame(null, $filter->getArchive());
        $filter->setArchive('Testfile.txt');
        self::assertSame('Testfile.txt', $filter->getArchive());
        self::assertSame('Testfile.txt', $filter->getOptions('archive'));
    }

    /**
     * Setting Archive
     */
    #[DataProvider('returnFilterType')]
    public function testCompressToFile(string $filterType): void
    {
        $filter  = new CompressFilter($filterType);
        $archive = $this->tmpDir . '/compressed.' . $filterType;
        $filter->setArchive($archive);

        $content = $filter('compress me');
        self::assertTrue($content);

        $filter2  = new CompressFilter($filterType);
        $content2 = $filter2->decompress($archive);
        self::assertSame('compress me', $content2);

        $filter3 = new CompressFilter($filterType);
        $filter3->setArchive($archive);
        $content3 = $filter3->decompress(null);
        self::assertSame('compress me', $content3);
    }

    /**
     * testing toString
     */
    #[DataProvider('returnFilterType')]
    public function testToString(string $filterType): void
    {
        $filter = new CompressFilter($filterType);
        self::assertEqualsIgnoringCase($filterType, $filter->toString());
    }

    /**
     * testing getAdapter
     */
    #[DataProvider('returnFilterType')]
    public function testGetAdapter(string $filterType): void
    {
        $filter  = new CompressFilter($filterType);
        $adapter = $filter->getAdapter();
        self::assertInstanceOf(CompressionAlgorithmInterface::class, $adapter);
        self::assertEqualsIgnoringCase($filterType, $filter->getAdapterName());
    }

    /**
     * Setting Adapter
     */
    public function testSetAdapter(): void
    {
        if (! extension_loaded('zlib')) {
            self::markTestSkipped('This filter is tested with the zlib extension');
        }

        $filter = new CompressFilter();
        self::assertSame('Gz', $filter->getAdapterName());

        /** @psalm-suppress InvalidArgument */
        $filter->setAdapter(Boolean::class);

        $this->expectException(Exception\InvalidArgumentException::class);
        $this->expectExceptionMessage('does not implement');
        $filter->getAdapter();
    }

    /**
     * Decompress archive
     */
    #[DataProvider('returnFilterType')]
    public function testDecompressArchive(string $filterType): void
    {
        $filter  = new CompressFilter($filterType);
        $archive = $this->tmpDir . '/compressed.' . $filterType;
        $filter->setArchive($archive);

        $content = $filter('compress me');
        self::assertTrue($content);

        $filter2  = new CompressFilter($filterType);
        $content2 = $filter2->decompress($archive);
        self::assertSame('compress me', $content2);
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

    /** @return iterable<array-key, array{0: string, 1: mixed}> */
    public static function returnUnfilteredDataProvider(): iterable
    {
        foreach (self::returnFilterType() as $parameters) {
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

    #[DataProvider('returnUnfilteredDataProvider')]
    public function testReturnUnfiltered(string $filterType, mixed $input): void
    {
        $filter = new CompressFilter($filterType);

        self::assertSame($input, $filter($input));
    }
}
