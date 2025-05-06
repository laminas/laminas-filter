<?php

declare(strict_types=1);

namespace Laminas\Filter\Compress;

use Laminas\Filter\Exception\RuntimeException;
use Laminas\Filter\File\FileInformation;

interface ArchiveAdapterResolverInterface
{
    /**
     * Return an adapter instance based on the filename extension of the given file path
     *
     * Implementations should also accept just the extension, i.e. 'zip' in a case-insensitive manner
     *
     * @throws RuntimeException If the matcher cannot figure out which adapter to use.
     */
    public function resolve(FileInformation $file): ArchiveAdapterInterface;
}
