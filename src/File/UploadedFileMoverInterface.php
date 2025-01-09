<?php

declare(strict_types=1);

namespace Laminas\Filter\File;

interface UploadedFileMoverInterface
{
    public function moveUploadedFile(string $sourceFile, string $targetFile): bool;
}
