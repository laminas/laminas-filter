<?php

declare(strict_types=1);

namespace Laminas\Filter\File;

use ErrorException;

use function is_uploaded_file;
use function move_uploaded_file;
use function rename;
use function restore_error_handler;
use function set_error_handler;

/** @internal */
final class MoveUploadedFile implements MoveUploadedFileInterface
{
    public function moveUploadedFile(string $sourceFile, string $targetFile): void
    {
        set_error_handler(static function (
            int $errorNumber,
            string $message,
            $file = null,
            $line = null,
        ): never {
            throw new ErrorException($message, $errorNumber, $errorNumber, $file, $line);
        });

        if (! is_uploaded_file($sourceFile)) {
            try {
                rename($sourceFile, $targetFile);

                return;
            } finally {
                restore_error_handler();
            }
        }

        try {
            move_uploaded_file($sourceFile, $targetFile);

            return;
        } finally {
            restore_error_handler();
        }
    }
}
