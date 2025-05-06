<?php

declare(strict_types=1);

namespace Laminas\Filter\Compress;

interface ArchiveAdapterInterface
{
    /**
     * Compress a file into the given archive
     *
     * @param non-empty-string $archivePath The full path to the target archive
     * @param non-empty-string $filePath The full path to the file to compress
     */
    public function archiveFile(string $archivePath, string $filePath): void;

    /**
     * Compress an arbitrary string as the contents of a file
     *
     * @param non-empty-string $archivePath The full path to the target archive
     * @param non-empty-string $fileName The basename of the target file within the archive
     * @param string $fileContents The contents of the compressed file
     */
    public function archiveStringToFile(string $archivePath, string $fileName, string $fileContents): void;

    /**
     * Compress the contents of a directory to an archive
     *
     * @param non-empty-string $archivePath The full path to the target archive
     * @param non-empty-string $directory The directory whose contents should be compressed
     */
    public function archiveDirectoryContents(string $archivePath, string $directory): void;

    /**
     * Decompress a file in the given archive
     *
     * @param non-empty-string $archivePath The full path of the archive to decompress
     * @param non-empty-string $targetDirectory The directory where the archive should be expanded
     */
    public function expandArchive(string $archivePath, string $targetDirectory): void;
}
