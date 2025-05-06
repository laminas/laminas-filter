<?php

declare(strict_types=1);

namespace Laminas\Filter\File;

use Laminas\Filter\Exception\InvalidArgumentException;
use Laminas\Filter\Exception\RuntimeException;
use Laminas\Filter\FilterInterface;

use function file_exists;
use function file_get_contents;
use function file_put_contents;
use function is_writable;
use function sprintf;

/** @internal */
final class FilterFileContents
{
    /** @param FilterInterface<string> $filter */
    public function __construct(private readonly FilterInterface $filter)
    {
    }

    /**
     * @throws InvalidArgumentException If no file exists.
     * @throws RuntimeException If the file cannot be written to or read from.
     */
    public function __invoke(string $filePath): void
    {
        if (! file_exists($filePath)) {
            throw new InvalidArgumentException(sprintf(
                'File %s not found',
                $filePath,
            ));
        }

        if (! is_writable($filePath)) {
            throw new RuntimeException(sprintf(
                'File "%s" is not writable',
                $filePath
            ));
        }

        $content = file_get_contents($filePath);
        if ($content === false) {
            throw new RuntimeException(sprintf(
                'The contents of "%s" could not be read',
                $filePath,
            ));
        }

        $result = file_put_contents(
            $filePath,
            $this->filter->filter($content),
        );

        if ($result === false) {
            throw new RuntimeException(sprintf(
                'The file "%s" could not be written to',
                $filePath,
            ));
        }
    }
}
