<?php

declare(strict_types=1);

namespace LaminasTest\Filter\File;

use ErrorException;
use Exception;
use Laminas\Filter\File\MoveUploadedFileInterface;

use function rename;
use function restore_error_handler;
use function set_error_handler;

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

        try {
            if (! rename($sourceFile, $targetFile)) {
                throw new Exception('Bad news');
            }
        } finally {
            restore_error_handler();
        }
    }
}
