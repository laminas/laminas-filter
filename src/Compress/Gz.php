<?php

namespace Laminas\Filter\Compress;

use Laminas\Filter\Exception;

use function file_exists;

/**
 * Compression adapter for Gzip (ZLib)
 */
class Gz extends AbstractCompressionAlgorithm
{
    /**
     * Compression Options
     * array(
     *     'level'    => Compression level 0-9
     *     'mode'     => Compression mode, can be 'compress', 'deflate'
     *     'archive'  => Archive to use
     * )
     *
     * @var array
     */
    protected $options = [
        'level'   => 9,
        'mode'    => 'compress',
        'archive' => '',
    ];

    /**
     * Class constructor
     *
     * @param null|array|\Traversable $options (Optional) Options to set
     * @throws Exception\ExtensionNotLoadedException if zlib extension not loaded
     */
    public function __construct($options = null)
    {
        if (! extension_loaded('zlib')) {
            throw new Exception\ExtensionNotLoadedException('This filter needs the zlib extension');
        }
        parent::__construct($options);
    }

    /**
     * Returns the set compression level
     *
     * @return int
     */
    public function getLevel()
    {
        return $this->options['level'];
    }

    /**
     * Sets a new compression level
     *
     * @param int $level
     * @throws Exception\InvalidArgumentException
     * @return self
     */
    public function setLevel($level)
    {
        if (($level < 0) || ($level > 9)) {
            throw new Exception\InvalidArgumentException('Level must be between 0 and 9');
        }

        $this->options['level'] = (int) $level;
        return $this;
    }

    /**
     * Returns the set compression mode
     *
     * @return string
     */
    public function getMode()
    {
        return $this->options['mode'];
    }

    /**
     * Sets a new compression mode
     *
     * @param  string $mode Supported are 'compress', 'deflate' and 'file'
     * @return self
     * @throws Exception\InvalidArgumentException for invalid $mode value
     */
    public function setMode($mode)
    {
        if ($mode !== 'compress' && $mode !== 'deflate') {
            throw new Exception\InvalidArgumentException('Given compression mode not supported');
        }

        $this->options['mode'] = $mode;
        return $this;
    }

    /**
     * Returns the set archive
     *
     * @return string
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
     * @throws Exception\RuntimeException if unable to open archive or error during decompression
     */
    public function compress($content)
    {
        $archive = $this->getArchive();
        if (! empty($archive)) {
            $file = gzopen($archive, 'w' . $this->getLevel());
            if (! $file) {
                throw new Exception\RuntimeException("Error opening the archive '" . $this->options['archive'] . "'");
            }

            gzwrite($file, $content);
            gzclose($file);
            $compressed = true;
        } elseif ($this->options['mode'] === 'deflate') {
            $compressed = gzdeflate($content, $this->getLevel());
        } else {
            $compressed = gzcompress($content, $this->getLevel());
        }

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
     * @throws Exception\RuntimeException if unable to open archive or error during decompression
     */
    public function decompress($content)
    {
        $archive = $this->getArchive();
        $mode    = $this->getMode();

        if ($content === null && ! file_exists($archive)) {
            return null;
        }

        //check if there are null byte characters before doing a file_exists check
        if ($content !== null && false === strpos($content, "\0") && file_exists($content)) {
            $archive = $content;
        }

        if (file_exists($archive)) {
            $handler = fopen($archive, 'rb');
            if (! $handler) {
                throw new Exception\RuntimeException("Error opening the archive '" . $archive . "'");
            }

            fseek($handler, -4, SEEK_END);
            $packet = fread($handler, 4);
            $bytes  = unpack('V', $packet);
            $size   = end($bytes);
            fclose($handler);

            $file       = gzopen($archive, 'r');
            $compressed = gzread($file, $size);
            gzclose($file);
        } elseif ($mode === 'deflate') {
            $compressed = gzinflate($content);
        } else {
            $compressed = gzuncompress($content);
        }

        if ($compressed === false) {
            throw new Exception\RuntimeException('Error during decompression');
        }

        return $compressed;
    }

    /**
     * Returns the adapter name
     *
     * @return string
     */
    public function toString(): string
    {
        return 'Gz';
    }
}
