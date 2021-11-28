<?php

declare(strict_types=1);

namespace Laminas\Filter\Compress;

/**
 * Compression interface
 */
interface CompressionAlgorithmInterface
{
    /**
     * Compresses $value with the defined settings
     *
     * @param  string $value Data to compress
     * @return string The compressed data
     */
    public function compress($value);

    /**
     * Decompresses $value with the defined settings
     *
     * @param  string $value Data to decompress
     * @return string The decompressed data
     */
    public function decompress($value);

    /**
     * Return the adapter name
     *
     * @return string
     */
    public function toString();
}
