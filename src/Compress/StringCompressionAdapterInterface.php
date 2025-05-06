<?php

declare(strict_types=1);

namespace Laminas\Filter\Compress;

use Laminas\Filter\Exception\RuntimeException;

interface StringCompressionAdapterInterface
{
    /**
     * Compress a string
     *
     * @param  string $value Data to compress
     * @return string The compressed data
     * @throws RuntimeException If compression is not successful.
     */
    public function compress(string $value): string;

    /**
     * Decompress a string
     *
     * @param  string $value Data to decompress
     * @return string The decompressed data
     * @throws RuntimeException If decompression is not successful.
     */
    public function decompress(string $value): string;
}
