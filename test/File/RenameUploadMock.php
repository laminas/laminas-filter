<?php

declare(strict_types=1);

namespace LaminasTest\Filter\File;

use Laminas\Filter\File\RenameUpload;

use function rename;

class RenameUploadMock extends RenameUpload
{
    /**
     * @param  string $sourceFile Source file path
     * @param  string $targetFile Target file path
     * @return bool
     */
    protected function moveUploadedFile($sourceFile, $targetFile)
    {
        return rename($sourceFile, $targetFile);
    }
}
