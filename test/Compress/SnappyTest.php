<?php

declare(strict_types=1);

namespace LaminasTest\Filter\Compress;

use Laminas\Filter\Compress\Snappy as SnappyCompression;
use Laminas\Filter\Exception;
use PHPUnit\Framework\TestCase;

use function extension_loaded;
use function restore_error_handler;
use function set_error_handler;

use const E_WARNING;

class SnappyTest extends TestCase
{
    public function setUp(): void
    {
        if (! extension_loaded('snappy')) {
            $this->markTestSkipped('This adapter needs the snappy extension');
        }
    }

    /**
     * Basic usage
     */
    public function testBasicUsage(): void
    {
        $filter = new SnappyCompression();

        $content = $filter->compress('compress me');
        $this->assertNotEquals('compress me', $content);

        $content = $filter->decompress($content);
        $this->assertSame('compress me', $content);
    }

    /**
     * Snappy should return NULL on invalid arguments.
     */
    public function testNonScalarInput(): void
    {
        $filter = new SnappyCompression();

        // restore_error_handler can emit an E_WARNING; let's ignore that, as
        // we want to test the returned value
        set_error_handler([$this, 'errorHandler'], E_WARNING);
        $content = $filter->compress([]);
        restore_error_handler();

        $this->assertNull($content);
    }

    /**
     * Snappy should handle empty input data correctly.
     */
    public function testEmptyString(): void
    {
        $filter = new SnappyCompression();

        $content = $filter->compress(false);
        $content = $filter->decompress($content);
        $this->assertSame('', $content, 'Snappy failed to decompress empty string.');
    }

    /**
     * Snappy should throw an exception when decompressing invalid data.
     */
    public function testInvalidData(): void
    {
        $filter = new SnappyCompression();

        $this->expectException(Exception\RuntimeException::class);
        $this->expectExceptionMessage('Error while decompressing.');

        // restore_error_handler can emit an E_WARNING; let's ignore that, as
        // we want to test the returned value
        set_error_handler([$this, 'errorHandler'], E_WARNING);
        $content = $filter->decompress('123');
        restore_error_handler();
    }

    /**
     * testing toString
     */
    public function testSnappyToString(): void
    {
        $filter = new SnappyCompression();
        $this->assertSame('Snappy', $filter->toString());
    }

    /**
     * Null error handler; used when wanting to ignore specific error types
     */
    public function errorHandler($errno, $errstr)
    {
    }
}
