<?php

declare(strict_types=1);

namespace Laminas\Filter\Compress;

use Laminas\Filter\Exception;
use Traversable;

use function extension_loaded;

/**
 * Compression adapter for php snappy (http://code.google.com/p/php-snappy/)
 */
class Snappy implements CompressionAlgorithmInterface
{
    /**
     * @param null|array|Traversable $options (Optional) Options to set
     * @throws Exception\ExtensionNotLoadedException If snappy extension not loaded.
     */
    public function __construct($options = null)
    {
        if (! extension_loaded('snappy')) {
            throw new Exception\ExtensionNotLoadedException('This filter needs the snappy extension');
        }
    }

    /**
     * Compresses the given content
     *
     * @param  string $content
     * @return string
     * @throws Exception\RuntimeException On memory, output length or data warning.
     */
    public function compress($content)
    {
        $compressed = snappy_compress($content);

        if ($compressed === false) {
            throw new Exception\RuntimeException('Error while compressing.');
        }

        return $compressed;
    }

    /**
     * Decompresses the given content
     *
     * @param  string $content
     * @return string
     * @throws Exception\RuntimeException On memory, output length or data warning.
     */
    public function decompress($content)
    {
        $compressed = snappy_uncompress($content);

        if ($compressed === false) {
            throw new Exception\RuntimeException('Error while decompressing.');
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
        return 'Snappy';
    }
}
