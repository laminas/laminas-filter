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
            self::markTestSkipped('This adapter needs the snappy extension');
        }
    }

    /**
     * Basic usage
     */
    public function testBasicUsage(): void
    {
        $filter = new SnappyCompression();

        $content = $filter->compress('compress me');
        self::assertNotEquals('compress me', $content);

        $content = $filter->decompress($content);
        self::assertSame('compress me', $content);
    }

    public function testANonStringWillYieldATypeErrorDuringCompression(): void
    {
        $this->expectError();
        $this->expectErrorMessage('snappy_compress : expects parameter to be string');
        /** @psalm-suppress InvalidArgument, InvalidCast */
        (new SnappyCompression())->compress([]);
    }

    public function testNonScalarInputCausesAnException(): void
    {
        $filter = new SnappyCompression();
        /** @psalm-suppress UnusedClosureParam */
        set_error_handler(static fn (int $num, string $msg): bool => true, E_WARNING);
        try {
            /** @psalm-suppress InvalidArgument, InvalidCast */
            $filter->compress([]);
            self::fail('No exception was thrown');
        } catch (Exception\RuntimeException $e) {
            self::assertStringContainsString('Error while compressing', $e->getMessage());
        } finally {
            restore_error_handler();
        }
    }

    /**
     * Snappy should handle empty input data correctly.
     */
    public function testEmptyString(): void
    {
        $filter = new SnappyCompression();

        $content = $filter->compress('');
        $content = $filter->decompress($content);
        self::assertSame('', $content, 'Snappy failed to decompress empty string.');
    }

    /**
     * Snappy should throw an exception when decompressing invalid data.
     */
    public function testInvalidData(): void
    {
        $filter = new SnappyCompression();

        $this->expectException(Exception\RuntimeException::class);
        $this->expectExceptionMessage('Error while decompressing.');

        /** @psalm-suppress UnusedClosureParam */
        set_error_handler(static fn (int $num, string $msg): bool => true, E_WARNING);
        $filter->decompress('123');
        restore_error_handler();
    }

    /**
     * testing toString
     */
    public function testSnappyToString(): void
    {
        $filter = new SnappyCompression();
        self::assertSame('Snappy', $filter->toString());
    }

    /**
     * Null error handler; used when wanting to ignore specific error types
     */
    public function errorHandler($errno, $errstr): void
    {
    }
}
