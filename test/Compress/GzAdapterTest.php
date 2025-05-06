<?php

declare(strict_types=1);

namespace LaminasTest\Filter\Compress;

use Laminas\Filter\Compress\GzAdapter;
use Laminas\Filter\Exception\RuntimeException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\WithoutErrorHandler;
use PHPUnit\Framework\TestCase;

use function extension_loaded;
use function range;
use function restore_error_handler;
use function set_error_handler;
use function sprintf;

use const E_WARNING;

final class GzAdapterTest extends TestCase
{
    public function setUp(): void
    {
        if (! extension_loaded('zlib')) {
            self::markTestSkipped('This adapter needs the zlib extension');
        }
    }

    public function testBasicUsage(): void
    {
        $adapter = new GzAdapter();

        $input        = 'Miss Piggy';
        $compressed   = $adapter->compress($input);
        $decompressed = $adapter->decompress($compressed);

        self::assertSame($input, $decompressed);
    }

    /** @return array<string, array{0: 'compress'|'deflate', 1: int<1,9>}> */
    public static function modeAndLevelProvider(): array
    {
        $cases = [];
        foreach (range(1, 9) as $level) {
            foreach (['compress', 'deflate'] as $mode) {
                $cases[sprintf(
                    '%s at %d',
                    $mode,
                    $level,
                )] = [$mode, $level];
            }
        }

        /** @psalm-var array<string, array{0: 'compress'|'deflate', 1: int<1,9>}> */
        return $cases;
    }

    /**
     * @param 'compress'|'deflate' $mode
     * @param int<1, 9> $level
     */
    #[DataProvider('modeAndLevelProvider')]
    public function testOptionsDoNotAffectBasicFunctionality(string $mode, int $level): void
    {
        $adapter = new GzAdapter([
            'level' => $level,
            'mode'  => $mode,
        ]);

        $input = 'Gonzo the Great';
        self::assertSame($input, $adapter->decompress($adapter->compress($input)));
    }

    #[WithoutErrorHandler]
    public function testDeCompressWithInvalidInput(): void
    {
        // Swallow PHP warnings to prevent test failures
        /** @psalm-suppress InvalidArgument */
        set_error_handler(fn (): bool|null => null, E_WARNING);
        try {
            (new GzAdapter())->decompress('Foo');
        } catch (RuntimeException $e) {
            self::assertSame('Error during decompression', $e->getMessage());
        } finally {
            restore_error_handler();
        }
    }
}
