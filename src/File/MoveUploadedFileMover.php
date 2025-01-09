<?php

declare(strict_types=1);

namespace Laminas\Filter\File;

use function move_uploaded_file;

final class MoveUploadedFileMover implements UploadedFileMoverInterface
{
    /**
     * Move the uploaded file with the safe function move_uploaded_file″
     */
    public function moveUploadedFile(string $sourceFile, string $targetFile): bool
    {
        return move_uploaded_file($sourceFile, $targetFile);
    }
}
