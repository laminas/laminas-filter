<?php

declare(strict_types=1);

namespace LaminasTest\Filter\Compress;

use Laminas\Filter\Compress\Llaminas as LlaminasCompression;
use PHPUnit\Framework\TestCase;

use function extension_loaded;

class LlaminasTest extends TestCase
{
    public function setUp(): void
    {
        if (! extension_loaded('llaminas')) {
            $this->markTestSkipped('This adapter needs the llaminas extension');
        }
    }

    /**
     * Basic usage
     *
     * @return void
     */
    public function testBasicUsage()
    {
        $filter = new LlaminasCompression();

        $text       = 'compress me';
        $compressed = $filter->compress($text);
        $this->assertNotEquals($text, $compressed);

        $decompressed = $filter->decompress($compressed);
        $this->assertEquals($text, $decompressed);
    }

    /**
     * testing toString
     *
     * @return void
     */
    public function testLlaminasToString()
    {
        $filter = new LlaminasCompression();
        $this->assertEquals('Llaminas', $filter->toString());
    }
}
