<?php

declare(strict_types=1);

namespace Laminas\Filter\File;

use finfo;
use Laminas\Filter\Exception\RuntimeException;
use Psr\Http\Message\UploadedFileInterface;

use function assert;
use function basename;
use function file_exists;
use function finfo_open;
use function is_array;
use function is_readable;
use function is_string;

use const FILEINFO_MIME_TYPE;

final class FileInformation
{
    public readonly string $baseName;
    public readonly bool $readable;
    private string|null $mediaType;

    /** @param non-empty-string $path */
    private function __construct(
        public readonly string $path,
        public readonly ?string $clientFileName,
        public readonly ?string $clientMediaType,
    ) {
        $this->readable  = is_readable($this->path);
        $this->baseName  = basename($this->path);
        $this->mediaType = null;
    }

    public function detectMimeType(): string
    {
        if ($this->mediaType === null) {
            $fileInfo = finfo_open(FILEINFO_MIME_TYPE);
            assert($fileInfo instanceof finfo);

            $mime = $fileInfo->file($this->path);
            assert(is_string($mime));

            $this->mediaType = $mime;
        }

        return $this->mediaType;
    }

    public static function factory(mixed $value): self
    {
        if (! self::isPossibleFile($value)) {
            throw new RuntimeException('Cannot detect any file information');
        }

        /** @psalm-var array<array-key, mixed>|non-empty-string|UploadedFileInterface $value */

        if ($value instanceof UploadedFileInterface) {
            $path = $value->getStream()->getMetadata('uri');
            assert(is_string($path) && $path !== '');

            return new self(
                $path,
                $value->getClientFilename(),
                $value->getClientMediaType(),
            );
        }

        if (is_string($value)) {
            return new self($value, null, null);
        }

        return self::fromSapiArray($value);
    }

    /** @param array<array-key, mixed> $value */
    private static function fromSapiArray(array $value): self
    {
        $clientName = $value['name'] ?? null;
        $clientType = $value['type'] ?? null;
        $path       = $value['tmp_name'] ?? null;

        assert(is_string($path) && $path !== '');
        assert(is_string($clientName));
        assert(is_string($clientType));

        return new self($path, $clientName, $clientType);
    }

    /**
     * Returns true when the argument is a PSR upload, a PHP $_FILES array or a file path to an on-disk file
     */
    public static function isPossibleFile(mixed $value): bool
    {
        if ($value instanceof UploadedFileInterface) {
            return true;
        }

        if (
            is_array($value)
            && isset($value['tmp_name'])
            && is_string($value['tmp_name'])
            && $value['tmp_name'] !== ''
            && file_exists($value['tmp_name'])
        ) {
            return true;
        }

        if (is_string($value) && $value !== '' && file_exists($value)) {
            return true;
        }

        return false;
    }
}
