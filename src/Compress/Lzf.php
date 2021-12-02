<?php

declare(strict_types=1);

namespace Laminas\Filter\Compress;

use Laminas\Filter\Exception;

use function extension_loaded;

/**
 * Compression adapter for Lzf
 */
class Lzf implements CompressionAlgorithmInterface
{
    /**
     * @param  null $options
     * @throws Exception\ExtensionNotLoadedException If lzf extension missing.
     */
    public function __construct($options = null)
    {
        if (! extension_loaded('lzf')) {
            throw new Exception\ExtensionNotLoadedException('This filter needs the lzf extension');
        }
    }

    /**
     * Compresses the given content
     *
     * @param  string $content
     * @return string
     * @throws Exception\RuntimeException If error occurs during compression.
     */
    public function compress($content)
    {
        $compressed = lzf_compress($content);
        if (! $compressed) {
            throw new Exception\RuntimeException('Error during compression');
        }

        return $compressed;
    }

    /**
     * Decompresses the given content
     *
     * @param  string $content
     * @return string
     * @throws Exception\RuntimeException If error occurs during decompression.
     */
    public function decompress($content)
    {
        $compressed = lzf_decompress($content);
        if (! $compressed) {
            throw new Exception\RuntimeException('Error during decompression');
        }

        return $compressed;
    }

    /**
     * Returns the adapter name
     *
     * @return string
     */
    public function toString()
    {
        return 'Lzf';
    }
}
