<?php

declare(strict_types=1);

namespace Laminas\Filter\Compress;

use Laminas\Filter\Exception\ExtensionNotLoadedException;
use Laminas\Filter\Exception\InvalidArgumentException;
use Laminas\Filter\Exception\RuntimeException;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use SplFileInfo;
use ZipArchive;

use function assert;
use function basename;
use function extension_loaded;
use function file_put_contents;
use function is_dir;
use function is_int;
use function is_string;
use function ltrim;
use function sprintf;
use function str_replace;
use function sys_get_temp_dir;

use const DIRECTORY_SEPARATOR;

final class ZipAdapter implements ArchiveAdapterInterface
{
    /**
     * @throws ExtensionNotLoadedException If zip extension not loaded.
     */
    public function __construct()
    {
        if (! extension_loaded('zip')) {
            throw new ExtensionNotLoadedException('This filter needs the zip extension');
        }
    }

    public function archiveFile(string $archivePath, string $filePath): void
    {
        $zip = $this->openArchive($archivePath);

        $result = $zip->addFile($filePath, basename($filePath));
        if ($result === false) {
            throw new RuntimeException(sprintf(
                'Failed to add the file %s to the archive',
                $filePath,
            ));
        }

        $zip->close();
    }

    public function archiveStringToFile(string $archivePath, string $fileName, string $fileContents): void
    {
        $filePath = sprintf('%s%s%s', sys_get_temp_dir(), DIRECTORY_SEPARATOR, basename($fileName));
        $result   = file_put_contents($filePath, $fileContents);
        if ($result === false) {
            throw new RuntimeException('Failed to write contents to a temporary file');
        }

        $this->archiveFile($archivePath, $filePath);
    }

    public function archiveDirectoryContents(string $archivePath, string $directory): void
    {
        if (! is_dir($directory)) {
            throw new InvalidArgumentException('The directory argument is not a directory');
        }

        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($directory, RecursiveDirectoryIterator::KEY_AS_PATHNAME),
            RecursiveIteratorIterator::SELF_FIRST,
        );

        $files = [];

        foreach ($iterator as $key => $item) {
            assert(is_string($key));
            assert($item instanceof SplFileInfo);

            if (! $item->isFile()) {
                continue;
            }

            $files[] = $key;
        }

        $zip = $this->openArchive($archivePath);

        foreach ($files as $filePath) {
            // Relative file path should use '/' separators inside the archive
            $entry = str_replace($directory, '', $filePath);
            $entry = ltrim(str_replace('\\', '/', $entry), '/');

            $result = $zip->addFile($filePath, $entry);
            if ($result === false) {
                throw new RuntimeException(sprintf(
                    'Failed to add the file %s to the archive',
                    $filePath,
                ));
            }
        }

        $zip->close();
    }

    public function expandArchive(string $archivePath, string $targetDirectory): void
    {
        $zip = new ZipArchive();
        $zip->open($archivePath);
        $result = $zip->extractTo($targetDirectory);
        $zip->close();
        if ($result === false) {
            throw new RuntimeException(sprintf(
                'Failed to extract archive to the target directory: %s',
                $targetDirectory,
            ));
        }
    }

    private function openArchive(string $archivePath): ZipArchive
    {
        $zip    = new ZipArchive();
        $result = $zip->open($archivePath, ZipArchive::CREATE | ZipArchive::OVERWRITE);

        if (is_int($result)) {
            throw new RuntimeException(sprintf(
                'The archive could not be opened. Error code %d',
                $result,
            ));
        }

        return $zip;
    }
}
