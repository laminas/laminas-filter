<?php

declare(strict_types=1);

namespace Laminas\Filter;

use Laminas\Filter\Compress\ArchiveAdapterInterface;
use Laminas\Filter\Compress\TarAdapter;
use Laminas\Filter\Compress\ZipAdapter;
use Laminas\Filter\Exception\RuntimeException;
use Laminas\Filter\File\FileInformation;

use function is_dir;
use function is_string;

/**
 * @psalm-type Options = array{
 *     archive: non-empty-string,
 *     adapter?: ArchiveAdapterInterface|'zip'|'tar'|null,
 *     fileName?: non-empty-string|null,
 * }
 * @implements FilterInterface<non-empty-string>
 */
final class CompressToArchive implements FilterInterface
{
    private readonly ArchiveAdapterInterface $adapter;
    /** @var non-empty-string */
    private readonly string $archive;
    /** @var non-empty-string|null */
    private readonly string|null $fileName;

    /** @param Options $options */
    public function __construct(array $options)
    {
        $adapter = $options['adapter'] ?? null;
        if (! $adapter instanceof ArchiveAdapterInterface) {
            $adapter ??= 'zip';
            $adapter   = match ($adapter) {
                'zip' => new ZipAdapter(),
                'tar' => new TarAdapter(),
            };
        }

        $this->adapter  = $adapter;
        $this->archive  = $options['archive'];
        $this->fileName = $options['fileName'] ?? null;
    }

    /**
     * Compress content to an archive
     *
     * Given a file path, the file will be compressed into the configured archive. When a directory path is
     * encountered, the directory contents will be compressed to the configured archive. When arbitrary strings are
     * received, they are treated as the contents of the file to compress. In this case, a target file name _must_ be
     * provided in the options.
     *
     * File paths can be passed as either a string, a PHP $_FILES array, or, a PSR UploadedFileInterface
     *
     * The return value is the full path to the archive containing the file/s or string provided, or, the un-filtered
     * input if filtering is not possible.
     *
     * @inheritDoc
     */
    public function filter(mixed $value): mixed
    {
        if (is_string($value) && $value !== '' && is_dir($value)) {
            /** @psalm-var non-empty-string $value This is required for now. Psalm cannot seem to infer the type here. */
            $this->adapter->archiveDirectoryContents($this->archive, $value);

            return $this->archive;
        }

        if (FileInformation::isPossibleFile($value)) {
            $file = FileInformation::factory($value);

            $this->adapter->archiveFile($this->archive, $file->path);

            return $this->archive;
        }

        if (! is_string($value)) {
            return $value;
        }

        if ($this->fileName === null) {
            throw new RuntimeException('The `fileName` option must be present when compressing arbitrary strings');
        }

        $this->adapter->archiveStringToFile($this->archive, $this->fileName, $value);

        return $this->archive;
    }

    public function __invoke(mixed $value): mixed
    {
        return $this->filter($value);
    }
}
