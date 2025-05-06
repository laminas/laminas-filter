<?php

declare(strict_types=1);

namespace Laminas\Filter\Compress;

use Archive_Tar;
use Laminas\Filter\Exception\ExtensionNotLoadedException;
use Laminas\Filter\Exception\InvalidArgumentException;
use Laminas\Filter\Exception\RuntimeException;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use SplFileInfo;

use function assert;
use function class_exists;
use function dirname;
use function extension_loaded;
use function file_exists;
use function is_dir;
use function is_string;
use function sprintf;
use function strtolower;

/**
 * @psalm-type Options = array{
 *     mode?: 'gz'|'bz2'|'GZ'|'BZ2'|null,
 * }
 */
final class TarAdapter implements ArchiveAdapterInterface
{
    /** @var 'gz'|'bz2' */
    private readonly string $mode;

    /**
     * @param Options $options
     * @throws ExtensionNotLoadedException If Archive_Tar component not available.
     */
    public function __construct(array $options = [])
    {
        if (! class_exists('Archive_Tar')) {
            throw new ExtensionNotLoadedException(
                'This filter needs PEAR\'s Archive_Tar component. '
                . 'Ensure loading Archive_Tar (registering autoload or require_once)',
            );
        }

        /** @psalm-var 'gz'|'bz2' $mode */
        $mode       = strtolower($options['mode'] ?? 'gz');
        $this->mode = $mode;

        if ($this->mode === 'bz2' && ! extension_loaded('bz2')) {
            throw new ExtensionNotLoadedException('This mode needs the bz2 extension');
        }

        if ($this->mode === 'gz' && ! extension_loaded('zlib')) {
            throw new ExtensionNotLoadedException('This mode needs the zlib extension');
        }
    }

    public function archiveFile(string $archivePath, string $filePath): void
    {
        if (! file_exists($filePath)) {
            throw new InvalidArgumentException(sprintf('The file %s does not exist', $filePath));
        }

        $archive = new Archive_Tar($archivePath, $this->mode);
        $result  = $archive->createModify([$filePath], '', dirname($filePath));
        if ($result === false) {
            throw new RuntimeException('Error creating the Tar archive');
        }
    }

    public function archiveDirectoryContents(string $archivePath, string $directory): void
    {
        if (! is_dir($directory)) {
            throw new InvalidArgumentException('The directory argument is not a directory');
        }

        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($directory, RecursiveDirectoryIterator::KEY_AS_PATHNAME),
            RecursiveIteratorIterator::SELF_FIRST,
        );

        $files = [];

        foreach ($iterator as $key => $item) {
            assert(is_string($key));
            assert($item instanceof SplFileInfo);

            if (! $item->isFile()) {
                continue;
            }

            $files[] = $key;
        }

        $archive = new Archive_Tar($archivePath, $this->mode);
        $result  = $archive->createModify($files, '', $directory);
        if ($result === false) {
            throw new RuntimeException('Error creating the Tar archive');
        }
    }

    public function archiveStringToFile(string $archivePath, string $fileName, string $fileContents): void
    {
        $archive = new Archive_Tar($archivePath, $this->mode);
        $result  = $archive->addString($fileName, $fileContents);
        if ($result === false) {
            throw new RuntimeException('Error creating the Tar archive');
        }
    }

    public function expandArchive(string $archivePath, string $targetDirectory): void
    {
        if (! file_exists($archivePath)) {
            throw new InvalidArgumentException(sprintf('An archive does not exist at %s', $archivePath));
        }

        $archive = new Archive_Tar($archivePath);

        $result = $archive->extract($targetDirectory);

        if ($result === false) {
            throw new RuntimeException('Error while extracting the Tar archive');
        }
    }
}
