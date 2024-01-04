<?php

declare(strict_types=1);

namespace LaminasTest\Filter;

use Laminas\Filter\Compress;
use Laminas\Filter\Decompress as DecompressFilter;
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

/** @psalm-import-type AdapterType from Compress */
class DecompressTest extends TestCase
{
    private string $tmpDir;

    public function setUp(): void
    {
        if (! extension_loaded('bz2')) {
            self::markTestSkipped('This filter is tested with the bz2 extension');
        }

        $this->tmpDir = sprintf('%s/%s', sys_get_temp_dir(), uniqid('laminasilter'));
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

    /** @return iterable<array-key, array{0: AdapterType}> */
    public static function returnFilterType(): iterable
    {
        if (extension_loaded('bz2')) {
            yield ['Bz2'];
        }
        if (extension_loaded('zlib')) {
            yield ['Gz'];
        }
    }

    /**
     * Basic usage
     *
     * @param AdapterType $filterType
     */
    #[DataProvider('returnFilterType')]
    public function testBasicUsage(string $filterType): void
    {
        $filter = new DecompressFilter($filterType);

        $text       = 'compress me';
        $compressed = $filter->compress($text);
        self::assertNotEquals($text, $compressed);

        $decompressed = $filter($compressed);
        self::assertSame($text, $decompressed);
    }

    /**
     * Setting Archive
     *
     * @param AdapterType $filterType
     */
    #[DataProvider('returnFilterType')]
    public function testCompressToFile(string $filterType): void
    {
        $filter  = new DecompressFilter($filterType);
        $archive = $this->tmpDir . '/compressed.' . $filterType;
        $filter->setArchive($archive);

        $content = $filter->compress('compress me');
        self::assertTrue($content);

        $filter2  = new DecompressFilter($filterType);
        $content2 = $filter2($archive);
        self::assertSame('compress me', $content2);

        $filter3 = new DecompressFilter($filterType);
        $filter3->setArchive($archive);
        $content3 = $filter3(null);
        self::assertSame('compress me', $content3);
    }

    /**
     * Basic usage
     *
     * @param AdapterType $filterType
     */
    #[DataProvider('returnFilterType')]
    public function testDecompressArchive(string $filterType): void
    {
        $filter  = new DecompressFilter($filterType);
        $archive = $this->tmpDir . '/compressed.' . $filterType;
        $filter->setArchive($archive);

        $content = $filter->compress('compress me');
        self::assertTrue($content);

        $filter2  = new DecompressFilter($filterType);
        $content2 = $filter2($archive);
        self::assertSame('compress me', $content2);
    }

    /**
     * @param AdapterType $filterType
     */
    #[DataProvider('returnFilterType')]
    public function testFilterMethodProxiesToDecompress(string $filterType): void
    {
        $filter  = new DecompressFilter($filterType);
        $archive = $this->tmpDir . '/compressed.' . $filterType;
        $filter->setArchive($archive);

        $content = $filter->compress('compress me');
        self::assertTrue($content);

        $filter2  = new DecompressFilter($filterType);
        $content2 = $filter2->filter($archive);
        self::assertSame('compress me', $content2);
    }

    /** @return iterable<array-key, array{0: string, 1: mixed}> */
    public static function returnUnfilteredDataProvider(): iterable
    {
        foreach (self::returnFilterType() as $parameter) {
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
     * @param AdapterType $filterType
     */
    #[DataProvider('returnUnfilteredDataProvider')]
    public function testReturnUnfiltered(string $filterType, mixed $input): void
    {
        $filter = new DecompressFilter($filterType);

        self::assertSame($input, $filter($input));
    }
}
