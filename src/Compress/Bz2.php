<?php

declare(strict_types=1);

namespace Laminas\Filter\Compress;

use Laminas\Filter\Exception;

use function bzclose;
use function bzcompress;
use function bzdecompress;
use function bzopen;
use function bzread;
use function bzwrite;
use function extension_loaded;
use function file_exists;
use function is_int;
use function str_contains;

/**
 * Compression adapter for Bz2
 *
 * @psalm-type Options = array{
 *     blocksize?: int,
 *     archive?: string|null,
 * }
 * @extends AbstractCompressionAlgorithm<Options>
 */
final class Bz2 extends AbstractCompressionAlgorithm
{
    private const DEFAULT_BLOCK_SIZE = 4;

    /**
     * Compression Options
     * array(
     *     'blocksize' => Blocksize to use from 0-9
     *     'archive'   => Archive to use
     * )
     *
     * @var Options
     */
    protected $options = [
        'blocksize' => self::DEFAULT_BLOCK_SIZE,
        'archive'   => null,
    ];

    /**
     * @param null|Options|iterable $options (Optional) Options to set
     * @throws Exception\ExtensionNotLoadedException If bz2 extension not loaded.
     */
    public function __construct($options = null)
    {
        if (! extension_loaded('bz2')) {
            throw new Exception\ExtensionNotLoadedException('This filter needs the bz2 extension');
        }
        parent::__construct($options);
    }

    /**
     * Returns the set blocksize
     *
     * @return int
     */
    public function getBlocksize()
    {
        return $this->options['blocksize'] ?? self::DEFAULT_BLOCK_SIZE;
    }

    /**
     * Sets a new blocksize
     *
     * @param  int $blocksize
     * @throws Exception\InvalidArgumentException
     * @return self
     */
    public function setBlocksize($blocksize)
    {
        if (($blocksize < 0) || ($blocksize > 9)) {
            throw new Exception\InvalidArgumentException('Blocksize must be between 0 and 9');
        }

        $this->options['blocksize'] = (int) $blocksize;
        return $this;
    }

    /**
     * Returns the set archive
     *
     * @return string|null
     */
    public function getArchive()
    {
        return $this->options['archive'];
    }

    /**
     * Sets the archive to use for de-/compression
     *
     * @param  string $archive Archive to use
     * @return self
     */
    public function setArchive($archive)
    {
        $this->options['archive'] = (string) $archive;
        return $this;
    }

    /**
     * Compresses the given content
     *
     * @param  string $content
     * @return string
     * @throws Exception\RuntimeException
     */
    public function compress($content)
    {
        $archive = $this->getArchive();
        if ($archive !== null) {
            $file = bzopen($archive, 'w');
            if (! $file) {
                throw new Exception\RuntimeException("Error opening the archive '" . $archive . "'");
            }

            bzwrite($file, $content);
            bzclose($file);
            $compressed = true;
        } else {
            $compressed = bzcompress($content, $this->getBlocksize());
        }

        if (is_int($compressed)) {
            throw new Exception\RuntimeException('Error during compression');
        }

        return $compressed;
    }

    /**
     * Decompresses the given content
     *
     * @param  string $content
     * @return string
     * @throws Exception\RuntimeException
     */
    public function decompress($content)
    {
        $archive = $this->getArchive();

        //check if there are null byte characters before doing a file_exists check
        if (null !== $content && ! str_contains($content, "\0") && file_exists($content)) {
            $archive = $content;
        }

        if (null !== $archive && file_exists($archive)) {
            $file = bzopen($archive, 'r');
            if (! $file) {
                throw new Exception\RuntimeException("Error opening the archive '" . $content . "'");
            }

            $compressed = bzread($file);
            bzclose($file);
        } elseif (null !== $content) {
            $compressed = bzdecompress($content);
        } else {
            // without strict types, bzdecompress(null) returns an empty string
            // we need to simulate this behaviour to prevent a BC break!
            $compressed = '';
        }

        if (is_int($compressed)) {
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
        return 'Bz2';
    }
}
