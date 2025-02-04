<?php

declare(strict_types=1);

namespace Laminas\Filter;

use function array_pop;
use function assert;
use function explode;
use function file_exists;
use function getcwd;
use function implode;
use function is_string;
use function realpath;
use function str_starts_with;

use const DIRECTORY_SEPARATOR;

/**
 * @psalm-type Options = array{
 *     exists?: bool,
 * }
 * @implements FilterInterface<string>
 */
final class RealPath implements FilterInterface
{
    private readonly bool $pathMustExist;

    /** @param Options $options */
    public function __construct(array $options = [])
    {
        $this->pathMustExist = $options['exists'] ?? true;
    }

    public function filter(mixed $value): mixed
    {
        if (! is_string($value)) {
            return $value;
        }

        if ($this->pathMustExist && ! file_exists($value)) {
            return $value;
        }

        $realPath = realpath($value);

        if ($realPath !== false) {
            return $realPath;
        }

        $path = $value;

        if (! str_starts_with($path, DIRECTORY_SEPARATOR)) {
            $cwd = getcwd();
            assert(is_string($cwd));

            $path = $cwd . DIRECTORY_SEPARATOR . $path;
        }

        $stack = [];
        $parts = explode(DIRECTORY_SEPARATOR, $path);

        foreach ($parts as $dir) {
            if ($dir !== '' && $dir !== '.') {
                if ($dir === '..') {
                    array_pop($stack);
                } else {
                    $stack[] = $dir;
                }
            }
        }

        return DIRECTORY_SEPARATOR . implode(DIRECTORY_SEPARATOR, $stack);
    }

    public function __invoke(mixed $value): mixed
    {
        return $this->filter($value);
    }
}
