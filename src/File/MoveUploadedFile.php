<?php

declare(strict_types=1);

namespace Laminas\Filter\File;

use ErrorException;
use Laminas\Filter\Exception\InvalidArgumentException;

use function is_uploaded_file;
use function move_uploaded_file;
use function restore_error_handler;
use function set_error_handler;

/** @internal */
final class MoveUploadedFile implements MoveUploadedFileInterface
{
    public function moveUploadedFile(string $sourceFile, string $targetFile): void
    {
        if (! is_uploaded_file($sourceFile)) {
            throw new InvalidArgumentException('The source file is not an uploaded file');
        }

        set_error_handler(static function (
            int $errorNumber,
            string $message,
            $file = null,
            $line = null,
        ): never {
            throw new ErrorException($message, $errorNumber, $errorNumber, $file, $line);
        });

        try {
            move_uploaded_file($sourceFile, $targetFile);

            return;
        } finally {
            restore_error_handler();
        }
    }
}
