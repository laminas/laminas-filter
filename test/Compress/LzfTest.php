<?php

declare(strict_types=1);

namespace LaminasTest\Filter\Compress;

use Laminas\Filter\Compress\Lzf;
use PHPUnit\Framework\TestCase;

use function extension_loaded;

class LzfTest extends TestCase
{
    public function setUp(): void
    {
        if (! extension_loaded('lzf')) {
            self::markTestSkipped('This adapter needs the lzf extension');
        }
    }

    /**
     * Basic usage
     */
    public function testBasicUsage(): void
    {
        $filter = new Lzf();

        $text       = 'compress me';
        $compressed = $filter->compress($text);
        self::assertNotEquals($text, $compressed);

        $decompressed = $filter->decompress($compressed);
        self::assertSame($text, $decompressed);
    }

    /**
     * testing toString
     */
    public function testLzfToString(): void
    {
        $filter = new Lzf();
        self::assertSame('Lzf', $filter->toString());
    }
}
