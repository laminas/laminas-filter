<?php

declare(strict_types=1);

namespace Laminas\Filter\Compress;

use Laminas\Filter\Exception;
use ZipArchive;

use function array_pop;
use function basename;
use function dir;
use function dirname;
use function extension_loaded;
use function file_exists;
use function is_dir;
use function is_file;
use function is_string;
use function realpath;
use function rtrim;
use function str_replace;
use function strrpos;
use function substr;

use const DIRECTORY_SEPARATOR;

/**
 * Compression adapter for zip
 *
 * @psalm-type Options = array{
 *     archive: string|null,
 *     password?: string|null,
 *     target: string|null,
 * }
 * @extends AbstractCompressionAlgorithm<Options>
 */
class Zip extends AbstractCompressionAlgorithm
{
    /**
     * Compression Options
     * array(
     *     'archive'  => Archive to use
     *     'password' => Password to use
     *     'target'   => Target to write the files to
     * )
     *
     * @var Options
     */
    protected $options = [
        'archive' => null,
        'target'  => null,
    ];

    /**
     * @param null|Options|iterable $options (Optional) Options to set
     * @throws Exception\ExtensionNotLoadedException If zip extension not loaded.
     */
    public function __construct($options = null)
    {
        if (! extension_loaded('zip')) {
            throw new Exception\ExtensionNotLoadedException('This filter needs the zip extension');
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
        $archive                  = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, (string) $archive);
        $this->options['archive'] = $archive;

        return $this;
    }

    /**
     * Returns the set targetpath
     *
     * @return string|null
     */
    public function getTarget()
    {
        return $this->options['target'];
    }

    /**
     * Sets the target to use
     *
     * @param  string $target
     * @throws Exception\InvalidArgumentException
     * @return self
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
     * Compresses the given content
     *
     * @param  string $content
     * @return string Compressed archive
     * @throws Exception\RuntimeException If unable to open zip archive, or error during compression.
     */
    public function compress($content)
    {
        $zip = new ZipArchive();
        $res = $zip->open($this->getArchive(), ZipArchive::CREATE | ZipArchive::OVERWRITE);

        if ($res !== true) {
            throw new Exception\RuntimeException($this->errorString($res));
        }

        if (file_exists($content)) {
            $content  = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, realpath($content));
            $basename = substr($content, strrpos($content, DIRECTORY_SEPARATOR) + 1);
            if (is_dir($content)) {
                $index    = strrpos($content, DIRECTORY_SEPARATOR) + 1;
                $content .= DIRECTORY_SEPARATOR;
                $stack    = [$content];
                while (! empty($stack)) {
                    $current = array_pop($stack);
                    $files   = [];

                    $dir = dir($current);
                    while (false !== ($node = $dir->read())) {
                        if ($node === '.' || $node === '..') {
                            continue;
                        }

                        if (is_dir($current . $node)) {
                            $stack[] = $current . $node . DIRECTORY_SEPARATOR;
                        }

                        if (is_file($current . $node)) {
                            $files[] = $node;
                        }
                    }

                    $local = substr($current, $index);
                    $zip->addEmptyDir(substr($local, 0, -1));

                    foreach ($files as $file) {
                        $zip->addFile($current . $file, $local . $file);
                        if ($res !== true) {
                            throw new Exception\RuntimeException($this->errorString($res));
                        }
                    }
                }
            } else {
                $res = $zip->addFile($content, $basename);
                if ($res !== true) {
                    throw new Exception\RuntimeException($this->errorString($res));
                }
            }
        } else {
            $file = $this->getTarget();
            if (is_string($file) && ! is_dir($file)) {
                $file = basename($file);
            } else {
                $file = 'zip.tmp';
            }

            $res = $zip->addFromString($file, $content);
            if ($res !== true) {
                throw new Exception\RuntimeException($this->errorString($res));
            }
        }

        $zip->close();
        return $this->options['archive'];
    }

    /**
     * Decompresses the given content
     *
     * @param  string $content
     * @return string
     * @throws Exception\RuntimeException If archive file not found, target directory not found,
     *                                    or error during decompression.
     */
    public function decompress($content)
    {
        $archive = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, realpath($content));

        if (empty($archive) || ! file_exists($archive)) {
            throw new Exception\RuntimeException('ZIP Archive not found');
        }

        $zip = new ZipArchive();
        $res = $zip->open($archive);

        $target = $this->getTarget();
        if (! empty($target) && ! is_dir($target)) {
            $target = dirname($target);
        }

        if (! empty($target)) {
            $target = rtrim($target, '/\\') . DIRECTORY_SEPARATOR;
        }

        if (empty($target) || ! is_dir($target)) {
            throw new Exception\RuntimeException('No target for ZIP decompression set');
        }

        if ($res !== true) {
            throw new Exception\RuntimeException($this->errorString($res));
        }

        $res = $zip->extractTo($target);
        if ($res !== true) {
            throw new Exception\RuntimeException($this->errorString($res));
        }

        $zip->close();
        return $target;
    }

    /**
     * Returns the proper string based on the given error constant
     *
     * @param  int $error
     * @return string
     */
    public function errorString($error)
    {
        return match ($error) {
            ZipArchive::ER_MULTIDISK => 'Multidisk ZIP Archives not supported',
            ZipArchive::ER_RENAME => 'Failed to rename the temporary file for ZIP',
            ZipArchive::ER_CLOSE => 'Failed to close the ZIP Archive',
            ZipArchive::ER_SEEK => 'Failure while seeking the ZIP Archive',
            ZipArchive::ER_READ => 'Failure while reading the ZIP Archive',
            ZipArchive::ER_WRITE => 'Failure while writing the ZIP Archive',
            ZipArchive::ER_CRC => 'CRC failure within the ZIP Archive',
            ZipArchive::ER_ZIPCLOSED => 'ZIP Archive already closed',
            ZipArchive::ER_NOENT => 'No such file within the ZIP Archive',
            ZipArchive::ER_EXISTS => 'ZIP Archive already exists',
            ZipArchive::ER_OPEN => 'Can not open ZIP Archive',
            ZipArchive::ER_TMPOPEN => 'Failure creating temporary ZIP Archive',
            ZipArchive::ER_ZLIB => 'ZLib Problem',
            ZipArchive::ER_MEMORY => 'Memory allocation problem while working on a ZIP Archive',
            ZipArchive::ER_CHANGED => 'ZIP Entry has been changed',
            ZipArchive::ER_COMPNOTSUPP => 'Compression method not supported within ZLib',
            ZipArchive::ER_EOF => 'Premature EOF within ZIP Archive',
            ZipArchive::ER_INVAL => 'Invalid argument for ZLIB',
            ZipArchive::ER_NOZIP => 'Given file is no zip archive',
            ZipArchive::ER_INTERNAL => 'Internal error while working on a ZIP Archive',
            ZipArchive::ER_INCONS => 'Inconsistent ZIP archive',
            ZipArchive::ER_REMOVE => 'Can not remove ZIP Archive',
            ZipArchive::ER_DELETED => 'ZIP Entry has been deleted',
            default => 'Unknown error within ZIP Archive',
        };
    }

    /**
     * Returns the adapter name
     *
     * @return string
     */
    public function toString()
    {
        return 'Zip';
    }
}
