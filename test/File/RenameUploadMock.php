<?php

declare(strict_types=1);

namespace LaminasTest\Filter\File;

use Laminas\Filter\File\RenameUpload;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Message\UploadedFileFactoryInterface;

use function rename;

/**
 * @see StreamFactoryInterface
 * @see UploadedFileFactoryInterface
 *
 * @psalm-type Options = array{
 *     target: string|null,
 *     use_upload_name: bool,
 *     use_upload_extension: bool,
 *     overwrite: bool,
 *     randomize: bool,
 *     stream_factory: StreamFactoryInterface|null,
 *     upload_file_factory: UploadedFileFactoryInterface|null,
 *     ...
 * }
 * @template TOptions of Options
 * @extends RenameUpload<TOptions>
 */
class RenameUploadMock extends RenameUpload
{
    /**
     * @param  string $sourceFile Source file path
     * @param  string $targetFile Target file path
     */
    protected function moveUploadedFile($sourceFile, $targetFile): bool
    {
        return rename($sourceFile, $targetFile);
    }
}
