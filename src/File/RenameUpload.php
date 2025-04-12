<?php

declare(strict_types=1);

namespace Laminas\Filter\File;

use Laminas\Filter\Exception\InvalidArgumentException;
use Laminas\Filter\Exception\RuntimeException;
use Laminas\Filter\FilterInterface;
use Throwable;

use function array_pop;
use function basename;
use function dirname;
use function explode;
use function file_exists;
use function implode;
use function is_dir;
use function pathinfo;
use function sprintf;
use function str_contains;
use function str_ends_with;
use function uniqid;
use function unlink;

use const DIRECTORY_SEPARATOR;
use const PATHINFO_DIRNAME;
use const PATHINFO_EXTENSION;

/**
 * @psalm-type Options = array{
 *     target?: non-empty-string,
 *     use_upload_name?: bool,
 *     use_upload_extension?: bool,
 *     overwrite?: bool,
 *     randomize?: bool,
 * }
 * @implements FilterInterface<string>
 */
final class RenameUpload implements FilterInterface
{
    private readonly string $targetDirectory;
    private readonly string $targetFilename;
    private readonly bool $useUploadName;
    private readonly bool $useUploadExtension;
    private readonly bool $overwrite;
    private readonly bool $randomize;
    private readonly MoveUploadedFileInterface $moveUploadedFile;

    /** @param Options $options */
    public function __construct(
        array $options = [],
        MoveUploadedFileInterface|null $moveUploadedFile = null,
    ) {
        $target                   = $this->resolveTargetOptions($options['target'] ?? '*');
        $this->targetDirectory    = $target['directory'];
        $this->targetFilename     = $target['filename'];
        $this->useUploadName      = $options['use_upload_name'] ?? false;
        $this->useUploadExtension = $options['use_upload_extension'] ?? false;
        $this->overwrite          = $options['overwrite'] ?? false;
        $this->randomize          = $options['randomize'] ?? false;
        $this->moveUploadedFile   = $moveUploadedFile ?? new MoveUploadedFile();
    }

    /**
     * Figure out a target directory and a target filename from the given string
     *
     * @param non-empty-string $option
     * @return array{directory: non-empty-string, filename: non-empty-string}
     */
    private function resolveTargetOptions(string $option): array
    {
        if ($option === '*') {
            return [
                'directory' => '*',
                'filename'  => '*',
            ];
        }

        if (is_dir($option)) {
            return [
                'directory' => $option,
                'filename'  => '*',
            ];
        }

        // Assume the last path component is a filename, if it contains a dot
        $file = basename($option);
        if (! str_contains($file, '.')) {
            $dir  = $option;
            $file = '*';
        } else {
            $dir = pathinfo($option, PATHINFO_DIRNAME);
        }

        if (! is_dir($dir) || $dir === '') {
            throw new InvalidArgumentException(sprintf(
                'Cannot resolve a target directory from the option: "%s"',
                $option,
            ));
        }

        return [
            'directory' => $dir,
            'filename'  => $file,
        ];
    }

    public function filter(mixed $value): mixed
    {
        if (! FileInformation::isPossibleFile($value)) {
            return $value;
        }

        $file   = FileInformation::factory($value);
        $target = $this->resolveTargetFilename($file);

        if (file_exists($target)) {
            if ($this->overwrite === false) {
                throw new RuntimeException(
                    sprintf("File '%s' could not be renamed. It already exists.", $target),
                );
            }

            unlink($target);
        }

        $this->moveUploadedFile($file->path, $target);

        return $target;
    }

    public function __invoke(mixed $value): mixed
    {
        return $this->filter($value);
    }

    /**
     * @throws RuntimeException
     * @param non-empty-string $sourceFile
     * @param non-empty-string $targetFile
     */
    private function moveUploadedFile(string $sourceFile, string $targetFile): void
    {
        try {
            $this->moveUploadedFile->moveUploadedFile($sourceFile, $targetFile);
        } catch (Throwable $e) {
            throw new RuntimeException(
                sprintf("File '%s' could not be renamed. An error occurred while processing the file.", $sourceFile),
                0,
                $e,
            );
        }
    }

    /** @return non-empty-string */
    private function resolveTargetFilename(FileInformation $file): string
    {
        $targetDirectory = $this->targetDirectory;
        if ($targetDirectory === '*') {
            $targetDirectory = dirname($file->path);
        }

        $targetFilename = $this->targetFilename;
        if ($targetFilename === '*') {
            if ($this->useUploadName && $file->clientFileName !== null) {
                $targetFilename = basename($file->clientFileName);
            } else {
                $targetFilename = $file->baseName;
            }
        }

        $extension = $this->targetFilename !== '*'
            ? pathinfo($this->targetFilename, PATHINFO_EXTENSION)
            : '';

        $extension = $this->useUploadExtension
            ? $this->resolveExtension($file)
            : $extension;

        if ($extension !== '' && ! str_ends_with($targetFilename, '.' . $extension)) {
            $targetFilename .= '.' . $extension;
        }

        if ($this->randomize) {
            $targetFilename = $this->randomizeFilename($targetFilename);
        }

        return $targetDirectory . DIRECTORY_SEPARATOR . $targetFilename;
    }

    /**
     * Return a filename extension, purely based on information in the upload
     *
     * Note: possibly empty string is returned
     */
    private function resolveExtension(FileInformation $file): string
    {
        if ($this->useUploadExtension && $file->clientFileName !== null) {
            $ext = pathinfo($file->clientFileName, PATHINFO_EXTENSION);
            if ($ext !== '') {
                return $ext;
            }
        }

        return pathinfo($file->path, PATHINFO_EXTENSION);
    }

    private function randomizeFilename(string $filename): string
    {
        $extension = pathinfo($filename, PATHINFO_EXTENSION);
        $extension = $extension === '' ? '' : '.' . $extension;
        $basename  = $filename;
        if ($extension !== '') {
            $parts = explode('.', $filename);
            array_pop($parts);
            $basename = implode('.', $parts);
        }

        return sprintf(
            '%s_%s%s',
            $basename,
            uniqid(),
            $extension,
        );
    }
}
