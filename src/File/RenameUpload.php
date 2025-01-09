<?php

declare(strict_types=1);

namespace Laminas\Filter\File;

use Laminas\Filter\Exception;
use Laminas\Filter\FilterInterface;
use Laminas\Stdlib\ErrorHandler;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Message\UploadedFileFactoryInterface;
use Psr\Http\Message\UploadedFileInterface;

use function array_key_exists;
use function assert;
use function basename;
use function file_exists;
use function filesize;
use function is_array;
use function is_dir;
use function is_int;
use function is_string;
use function pathinfo;
use function spl_object_hash;
use function sprintf;
use function str_replace;
use function strlen;
use function uniqid;
use function unlink;

use const DIRECTORY_SEPARATOR;
use const PATHINFO_DIRNAME;
use const PATHINFO_EXTENSION;
use const PATHINFO_FILENAME;
use const UPLOAD_ERR_OK;

/**
 * @psalm-type UploadedFile = array{
 *     name:string,
 *     tmp_name: string
 * }
 * @psalm-type Options = array{
 *     target_directory?: string,
 *     use_upload_name?: bool,
 *     use_upload_extension?: bool,
 *     overwrite?: bool,
 *     randomize?: bool,
 *     stream_factory?: StreamFactoryInterface,
 *     upload_file_factory?: UploadedFileFactoryInterface,
 *     upload_file_mover?: UploadedFileMoverInterface,
 * }
 * @implements FilterInterface<mixed>
 */
final class RenameUpload implements FilterInterface
{
    private readonly string $targetDirectory;
    private readonly bool $useUploadName;
    private readonly bool $useUploadExtension;
    private readonly bool $overwrite;
    private readonly bool $randomize;
    private readonly StreamFactoryInterface|null $streamFactory;
    private readonly UploadedFileFactoryInterface|null $uploadedFileFactory;

    /**
     * Store already filtered values, so we can filter multiple
     * times the same file without being block by move_uploaded_file
     * internal checks
     *
     * @var array{
     *     psr7: array<string, UploadedFileInterface>,
     *     upload: array<string, UploadedFile>,
     *     string: array<string, string>
     * }
     */
    private array $alreadyFilteredByType = [
        'psr7'   => [],
        'upload' => [],
        'string' => [],
    ];

    private UploadedFileMoverInterface $uploadedFileMover;

    /**
     * @param Options $options
     */
    public function __construct(array $options = [])
    {
        $this->targetDirectory     = $options['target_directory'] ?? '*';
        $this->useUploadName       = $options['use_upload_name'] ?? false;
        $this->useUploadExtension  = $options['use_upload_extension'] ?? false;
        $this->overwrite           = $options['overwrite'] ?? false;
        $this->randomize           = $options['randomize'] ?? false;
        $this->streamFactory       = $options['stream_factory'] ?? null;
        $this->uploadedFileFactory = $options['upload_file_factory'] ?? null;
        $this->uploadedFileMover   = $options['upload_file_mover'] ?? new MoveUploadedFileMover();
    }

    /**
     * @template T
     * @param T $value
     * @return (T is UploadedFileInterface
     *  ? UploadedFileInterface
     *  : (T is array ? array : (T is string ? string : mixed))
     * )
     */
    public function filter(mixed $value): mixed
    {
        // PSR-7 uploaded file
        if ($value instanceof UploadedFileInterface) {
            return $this->filterPsr7UploadedFile($value);
        }

        // File upload via traditional SAPI
        if (
            is_array($value)
            && array_key_exists('tmp_name', $value)
            && array_key_exists('name', $value)
        ) {
            /** @var UploadedFile $uploadedFile */
            $uploadedFile = $value;
            return $this->filterSapiUploadedFile($uploadedFile);
        }

        // String filename
        if (is_string($value)) {
            return $this->filterStringFilename($value);
        }

        return $value;
    }

    /**
     * @psalm-suppress MixedReturnStatement
     * @template T
     * @param T $value
     * @return (T is UploadedFileInterface
     *  ? UploadedFileInterface
     *  : (T is array ? array : (T is string ? string : mixed))
     * )
     */
    public function __invoke(mixed $value): mixed
    {
        return $this->filter($value);
    }

    /**
     * @throws Exception\RuntimeException
     */
    private function moveUploadedFile(string $sourceFile, string $targetFile): void
    {
        ErrorHandler::start();
        $result           = $this->uploadedFileMover->moveUploadedFile($sourceFile, $targetFile);
        $warningException = ErrorHandler::stop();
        if (! $result || null !== $warningException) {
            throw new Exception\RuntimeException(
                sprintf("File '%s' could not be renamed. An error occurred while processing the file.", $sourceFile),
                0,
                $warningException
            );
        }
    }

    /**
     * @throws Exception\InvalidArgumentException
     */
    private function checkFileExists(string $targetFile): void
    {
        if (! file_exists($targetFile)) {
            return;
        }

        if (! $this->overwrite) {
            throw new Exception\InvalidArgumentException(
                sprintf("File '%s' could not be renamed. It already exists.", $targetFile)
            );
        }

        unlink($targetFile);
    }

    private function getFinalTarget(string $source, string|null $clientFileName): string
    {
        $target = $this->targetDirectory;
        if ($target === '*') {
            $target = $source;
        }

        // Get the target directory
        if (is_dir($target)) {
            $targetDir = $target;
            $last      = $target[strlen($target) - 1];
            if (($last !== '/') && ($last !== '\\')) {
                $targetDir .= DIRECTORY_SEPARATOR;
            }
        } else {
            $targetDir = pathinfo($target, PATHINFO_DIRNAME) . DIRECTORY_SEPARATOR;
        }

        // Get the target filename
        if ($this->useUploadName && $clientFileName !== null) {
            $targetFile = basename($clientFileName);
        } elseif (! is_dir($target)) {
            $targetFile = basename($target);
            if ($this->useUploadExtension && ! $this->randomize && $clientFileName !== null) {
                $targetFilename  = pathinfo($targetFile, PATHINFO_FILENAME);
                $sourceExtension = pathinfo($clientFileName, PATHINFO_EXTENSION);
                if ($sourceExtension !== '') {
                    $targetFile = $targetFilename . '.' . $sourceExtension;
                }
            }
        } else {
            $targetFile = basename($source);
        }

        if ($this->randomize) {
            $targetFile = $this->applyRandomToFilename($clientFileName, $targetFile);
        }

        return $targetDir . $targetFile;
    }

    private function applyRandomToFilename(string|null $source, string $filename): string
    {
        $info     = pathinfo($filename);
        $filename = $info['filename'] . str_replace('.', '_', uniqid('_', true));

        $sourceinfo = $source === null ? [] : pathinfo($source);

        $extension = '';
        if ($this->useUploadExtension && isset($sourceinfo['extension'])) {
            $extension .= '.' . $sourceinfo['extension'];
        } elseif (isset($info['extension'])) {
            $extension .= '.' . $info['extension'];
        }

        return $filename . $extension;
    }

    private function filterStringFilename(string $fileName): string
    {
        if (isset($this->alreadyFilteredByType['string'][$fileName])) {
            return $this->alreadyFilteredByType['string'][$fileName];
        }

        $targetFile = $this->getFinalTarget($fileName, $fileName);
        if ($fileName === $targetFile || ! file_exists($fileName)) {
            return $fileName;
        }

        $this->checkFileExists($targetFile);
        $this->moveUploadedFile($fileName, $targetFile);
        $this->alreadyFilteredByType['string'][$fileName] = $targetFile;

        return $this->alreadyFilteredByType['string'][$fileName];
    }

    /**
     * @param UploadedFile $fileData
     * @return UploadedFile
     */
    private function filterSapiUploadedFile(array $fileData): array
    {
        $sourceFile = $fileData['tmp_name'];

        if (isset($this->alreadyFilteredByType['upload'][$sourceFile])) {
            return $this->alreadyFilteredByType['upload'][$sourceFile];
        }

        $clientFilename = $fileData['name'];

        $targetFile = $this->getFinalTarget($sourceFile, $clientFilename);
        if ($sourceFile === $targetFile || ! file_exists($sourceFile)) {
            return $fileData;
        }

        $this->checkFileExists($targetFile);
        $this->moveUploadedFile($sourceFile, $targetFile);

        $this->alreadyFilteredByType['upload'][$sourceFile]             = $fileData;
        $this->alreadyFilteredByType['upload'][$sourceFile]['tmp_name'] = $targetFile;

        return $this->alreadyFilteredByType['upload'][$sourceFile];
    }

    /**
     * @throws Exception\RuntimeException If no stream factory is composed in the filter.
     * @throws Exception\RuntimeException If no uploaded file factory is composed in the filter.
     */
    private function filterPsr7UploadedFile(UploadedFileInterface $uploadedFile): UploadedFileInterface
    {
        $alreadyFilteredKey = spl_object_hash($uploadedFile);

        if (isset($this->alreadyFilteredByType['psr7'][$alreadyFilteredKey])) {
            return $this->alreadyFilteredByType['psr7'][$alreadyFilteredKey];
        }

        $sourceFile = $uploadedFile->getStream()->getMetadata('uri');
        if (! is_string($sourceFile)) {
            throw new Exception\RuntimeException('UploadedFile doesn\'t contains the uri metadata');
        }

        $clientFilename = $uploadedFile->getClientFilename();
        $targetFile     = $this->getFinalTarget($sourceFile, $clientFilename);

        if ($sourceFile === $targetFile || ! file_exists($sourceFile)) {
            return $uploadedFile;
        }

        $this->checkFileExists($targetFile);
        $uploadedFile->moveTo($targetFile);

        $streamFactory = $this->streamFactory;
        if (! $streamFactory) {
            throw new Exception\RuntimeException(sprintf(
                'No PSR-17 %s present; cannot filter file. Please pass the stream_factory'
                . ' option with a %s instance when creating the filter for use with PSR-7.',
                StreamFactoryInterface::class,
                StreamFactoryInterface::class
            ));
        }

        $stream = $streamFactory->createStreamFromFile($targetFile);

        $uploadedFileFactory = $this->uploadedFileFactory;
        if (! $uploadedFileFactory) {
            throw new Exception\RuntimeException(sprintf(
                'No PSR-17 %s present; cannot filter file. Please pass the upload_file_factory'
                . ' option with a %s instance when creating the filter for use with PSR-7.',
                UploadedFileFactoryInterface::class,
                UploadedFileFactoryInterface::class
            ));
        }

        $size = filesize($targetFile);
        assert(is_int($size));

        $this->alreadyFilteredByType['psr7'][$alreadyFilteredKey] = $uploadedFileFactory->createUploadedFile(
            $stream,
            $size,
            UPLOAD_ERR_OK,
            $uploadedFile->getClientFilename(),
            $uploadedFile->getClientMediaType()
        );

        return $this->alreadyFilteredByType['psr7'][$alreadyFilteredKey];
    }
}
