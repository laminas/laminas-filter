<?php

/**
 * @see       https://github.com/laminas/laminas-filter for the canonical source repository
 * @copyright https://github.com/laminas/laminas-filter/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-filter/blob/master/LICENSE.md New BSD License
 */

namespace LaminasTest\Filter\File;

use Laminas\Filter\File\RenameUpload;

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
