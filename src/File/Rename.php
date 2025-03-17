<?php

declare(strict_types=1);

namespace Laminas\Filter\File;

use Laminas\Filter\Exception;
use Laminas\Filter\FilterInterface;

use function array_is_list;
use function file_exists;
use function fnmatch;
use function is_dir;
use function is_string;
use function is_writable;
use function pathinfo;
use function rename;
use function sprintf;
use function uniqid;
use function unlink;

/**
 * @psalm-type OptionsSet = array{
 *     match?: non-empty-string,
 *     target_directory?: non-empty-string,
 *     rename_to?: string,
 *     overwrite?: bool,
 *     randomize?: bool
 * }
 * @psalm-type Options = OptionsSet|list<OptionsSet>
 * @psalm-type DefaultedOptionsSet = array{
 *      match: non-empty-string,
 *      target_directory: non-empty-string,
 *      rename_to: string,
 *      overwrite: bool,
 *      randomize: bool
 *  }
 * @implements FilterInterface<string>
 */
final class Rename implements FilterInterface
{
    /** @var DefaultedOptionsSet[] */
    private readonly array $options;

    /** @param Options $options */
    public function __construct(array $options = [])
    {
        $defaultedOptions = [];

        if (array_is_list($options)) {
            /** @psalm-var OptionsSet $option */
            foreach ($options as $option) {
                $defaultedOptions[] = $this->validateAndDefaultOptions($option);
            }
        } else {
            /** @psalm-var OptionsSet $options */
            $defaultedOptions[] = $this->validateAndDefaultOptions($options);
        }

        $this->options = $defaultedOptions;
    }

    /**
     * @param OptionsSet $options
     * @return DefaultedOptionsSet
     */
    private function validateAndDefaultOptions(array $options): array
    {
        $target = $options['target_directory'] ?? '*';

        if ($target !== '*') {
            if (! is_dir($target)) {
                throw new Exception\InvalidArgumentException(sprintf(
                    'The target directory "%s" does not exist',
                    $target
                ));
            }

            if (! is_writable($target)) {
                throw new Exception\InvalidArgumentException(sprintf(
                    'The target directory "%s" is not writable',
                    $target
                ));
            }
        }

        return [
            'match'            => $options['match'] ?? '*',
            'target_directory' => $target,
            'rename_to'        => $options['rename_to'] ?? '*',
            'overwrite'        => $options['overwrite'] ?? false,
            'randomize'        => $options['randomize'] ?? false,
        ];
    }

    /**
     * @param DefaultedOptionsSet $matchingOptions
     * @throws Exception\InvalidArgumentException If the target file already exists.
     */
    private function renameFile(string $sourceFilePath, array $matchingOptions): string
    {
        $file = $this->getFileName($sourceFilePath, $matchingOptions);

        if ($file === $sourceFilePath) {
            return $file;
        }

        if ($matchingOptions['overwrite'] && file_exists($file)) {
            unlink($file);
        }

        if (file_exists($file)) {
            throw new Exception\InvalidArgumentException(sprintf(
                '"File "%s" could not be renamed to "%s"; target file already exists',
                $sourceFilePath,
                $file
            ));
        }

        $result = rename($sourceFilePath, $file);

        if ($result !== true) {
            throw new Exception\RuntimeException(
                sprintf(
                    "File '%s' could not be renamed. "
                    . "An error occurred while processing the file.",
                    $sourceFilePath
                )
            );
        }

        return $file;
    }

    /**
     * @throws Exception\RuntimeException
     */
    public function filter(mixed $value): mixed
    {
        if (! is_string($value)) {
            return $value;
        }

        if (! file_exists($value)) {
            return $value;
        }

        foreach ($this->options as $option) {
            if (fnmatch($option['match'], $value)) {
                return $this->renameFile($value, $option);
            }
        }

        return $value;
    }

    /**
     * @param DefaultedOptionsSet $matchingOptions
     */
    private function getFileName(string $file, array $matchingOptions): string
    {
        $fileInfo = pathinfo($file);

        $targetName = $matchingOptions['rename_to'] === '*' ? $fileInfo['basename'] : $matchingOptions['rename_to'];
        $targetDir  = $matchingOptions['target_directory'] === '*' ?
            $fileInfo['dirname'] : $matchingOptions['target_directory'];

        $target = $targetDir . '/' . $targetName;

        if ($matchingOptions['randomize']) {
            $info      = pathinfo($target);
            $newTarget = $info['dirname'] . '/' . $info['filename'] . uniqid('_');
            if (isset($info['extension'])) {
                $newTarget .= '.' . $info['extension'];
            }
            $target = $newTarget;
        }

        return $target;
    }

    public function __invoke(mixed $value): mixed
    {
        return $this->filter($value);
    }
}
