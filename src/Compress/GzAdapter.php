<?php

declare(strict_types=1);

namespace Laminas\Filter\Compress;

use Laminas\Filter\Exception\ExtensionNotLoadedException;
use Laminas\Filter\Exception\RuntimeException;

use function assert;
use function extension_loaded;
use function gzcompress;
use function gzdeflate;
use function gzinflate;
use function gzuncompress;

/**
 * Compression adapter for Gzip (ZLib)
 *
 * @psalm-type Options = array{
 *     level?: int<0, 9>|null,
 *     mode?: 'deflate'|'compress',
 * }
 */
final class GzAdapter implements StringCompressionAdapterInterface
{
    /**
     * Compression level
     *
     * -1 indicates the PHP default (Probably 6), 0 = no compression and 9 = max compression
     *
     * @var int<-1, 9>
     */
    private readonly int $level;

    /** @var 'deflate'|'compress' */
    private readonly string $mode;

    /**
     * @param Options $options (Optional) Options to set
     * @throws ExtensionNotLoadedException If zlib extension not loaded.
     */
    public function __construct(array $options = [])
    {
        if (! extension_loaded('zlib')) {
            throw new ExtensionNotLoadedException('This filter needs the zlib extension');
        }

        $this->level = $options['level'] ?? -1;
        $this->mode  = $options['mode'] ?? 'compress';
    }

    public function compress(string $value): string
    {
        $compressed = $this->mode === 'compress'
            ? gzcompress($value, $this->level)
            : gzdeflate($value, $this->level);

        if ($compressed === false) {
            throw new RuntimeException('Compression failed');
        }

        assert($compressed !== '');

        return $compressed;
    }

    public function decompress(string $value): string
    {
        $decompressed = $this->mode === 'compress'
            ? gzuncompress($value)
            : gzinflate($value);

        if ($decompressed === false) {
            throw new RuntimeException('Error during decompression');
        }

        assert($decompressed !== '');

        return $decompressed;
    }
}
