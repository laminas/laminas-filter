<?php

declare(strict_types=1);

namespace Laminas\Filter\Compress;

use Archive_Tar;
use Laminas\Filter\Exception;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

use function class_exists;
use function dirname;
use function extension_loaded;
use function file_exists;
use function file_put_contents;
use function is_dir;
use function realpath;
use function str_replace;
use function strtolower;

use const DIRECTORY_SEPARATOR;

/**
 * Compression adapter for Tar
 *
 * @psalm-type Options = array{
 *     archive?: string|null,
 *     target?: string,
 *     mode?: 'gz'|'bz2'|null,
 * }
 * @extends AbstractCompressionAlgorithm<Options>
 */
final class Tar extends AbstractCompressionAlgorithm
{
    /**
     * Compression Options
     * array(
     *     'archive' => Archive to use
     *     'target'  => Target to write the files to
     * )
     *
     * @var Options
     */
    protected $options = [
        'archive' => null,
        'target'  => '.',
        'mode'    => null,
    ];

    /**
     * @param Options $options (Optional) Options to set
     * @throws Exception\ExtensionNotLoadedException If Archive_Tar component not available.
     */
    public function __construct($options = null)
    {
        if (! class_exists('Archive_Tar')) {
            throw new Exception\ExtensionNotLoadedException(
                'This filter needs PEAR\'s Archive_Tar component. '
                . 'Ensure loading Archive_Tar (registering autoload or require_once)'
            );
        }

        parent::__construct($options);
    }

    /**
     * Returns the set archive
     *
     * @return string|null
     */
    public function getArchive()
    {
        return $this->options['archive'] ?? null;
    }

    /**
     * Sets the archive to use for de-/compression
     *
     * @param  string $archive Archive to use
     * @return self
     */
    public function setArchive($archive)
    {
        $archive                  = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, (string) $archive);
        $this->options['archive'] = $archive;

        return $this;
    }

    /**
     * Returns the set target path
     *
     * @return string
     */
    public function getTarget()
    {
        return $this->options['target'];
    }

    /**
     * Sets the target path to use
     *
     * @param  string $target
     * @return self
     * @throws Exception\InvalidArgumentException If target path does not exist.
     */
    public function setTarget($target)
    {
        if (! file_exists(dirname($target))) {
            throw new Exception\InvalidArgumentException("The directory '$target' does not exist");
        }

        $target                  = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, (string) $target);
        $this->options['target'] = $target;
        return $this;
    }

    /**
     * Returns the set compression mode
     *
     * @return string|null
     */
    public function getMode()
    {
        return $this->options['mode'] ?? null;
    }

    /**
     * Compression mode to use
     *
     * Either Gz or Bz2.
     *
     * @param string $mode
     * @return self
     * @throws Exception\InvalidArgumentException For invalid $mode values.
     * @throws Exception\ExtensionNotLoadedException If bz2 mode selected but extension not loaded.
     * @throws Exception\ExtensionNotLoadedException If gz mode selected but extension not loaded.
     */
    public function setMode($mode)
    {
        $mode = strtolower($mode);
        if ($mode !== 'bz2' && $mode !== 'gz') {
            throw new Exception\InvalidArgumentException("The mode '$mode' is unknown");
        }

        if ($mode === 'bz2' && ! extension_loaded('bz2')) {
            throw new Exception\ExtensionNotLoadedException('This mode needs the bz2 extension');
        }

        if ($mode === 'gz' && ! extension_loaded('zlib')) {
            throw new Exception\ExtensionNotLoadedException('This mode needs the zlib extension');
        }

        $this->options['mode'] = $mode;
        return $this;
    }

    /**
     * Compresses the given content
     *
     * @param  string $content
     * @return string
     * @throws Exception\RuntimeException If unable to create temporary file.
     * @throws Exception\RuntimeException If unable to create archive.
     */
    public function compress($content)
    {
        $archive = new Archive_Tar($this->getArchive(), $this->getMode());
        if (! file_exists($content)) {
            $file = $this->getTarget();
            if (is_dir($file)) {
                $file .= DIRECTORY_SEPARATOR . 'tar.tmp';
            }

            $result = file_put_contents($file, $content);
            if ($result === false) {
                throw new Exception\RuntimeException('Error creating the temporary file');
            }

            $content = $file;
        }

        if (is_dir($content)) {
            // collect all file infos
            foreach (
                new RecursiveIteratorIterator(
                    new RecursiveDirectoryIterator($content, RecursiveDirectoryIterator::KEY_AS_PATHNAME),
                    RecursiveIteratorIterator::SELF_FIRST
                ) as $directory => $info
            ) {
                if ($info->isFile()) {
                    $file[] = $directory;
                }
            }

            $content = $file;
        }

        $result = $archive->create($content);
        if ($result === false) {
            throw new Exception\RuntimeException('Error creating the Tar archive');
        }

        return $this->getArchive();
    }

    /**
     * Decompresses the given content
     *
     * @param  string $content
     * @return string
     * @throws Exception\RuntimeException If unable to find archive.
     * @throws Exception\RuntimeException If error occurs decompressing archive.
     */
    public function decompress($content)
    {
        $archive = (string) $this->getArchive();
        if (file_exists($content)) {
            $archive = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, realpath($content));
        } elseif ($archive === '' || ! file_exists($archive)) {
            throw new Exception\RuntimeException('Tar Archive not found');
        }

        $archive = new Archive_Tar($archive, $this->getMode());
        $target  = $this->getTarget();
        if (! is_dir($target)) {
            $target = dirname($target) . DIRECTORY_SEPARATOR;
        }

        $result = $archive->extract($target);
        if ($result === false) {
            throw new Exception\RuntimeException('Error while extracting the Tar archive');
        }

        return $target;
    }

    /**
     * Returns the adapter name
     *
     * @return string
     */
    public function toString()
    {
        return 'Tar';
    }
}
