<?php

declare(strict_types=1);

namespace Laminas\Filter\File;

use Throwable;

interface MoveUploadedFileInterface
{
    /**
     * @param non-empty-string $sourceFile
     * @param non-empty-string $targetFile
     * @throws Throwable If any kind of failure occurs that prevents the file from being moved.
     */
    public function moveUploadedFile(string $sourceFile, string $targetFile): void;
}
