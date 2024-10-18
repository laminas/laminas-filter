<?php

declare(strict_types=1);

namespace Laminas\Filter\File;

use Laminas\Filter;
use Laminas\Filter\Exception;
use Laminas\Stdlib\ArrayUtils;
use Traversable;

use function basename;
use function count;
use function file_exists;
use function is_array;
use function is_bool;
use function is_dir;
use function is_scalar;
use function is_string;
use function pathinfo;
use function realpath;
use function rename;
use function sprintf;
use function strlen;
use function uniqid;
use function unlink;

use const DIRECTORY_SEPARATOR;

/**
 * @psalm-type Options = array{
 *     file?: array{source?: string, target?: string, overwrite?: bool, randomize?: bool},
 *     ...
 * }
 * @template TOptions of Options
 * @template-extends Filter\AbstractFilter<TOptions>
 */
final class Rename extends Filter\AbstractFilter
{
    /**
     * Internal array of array(source, target, overwrite)
     *
     * @var list<array{source: string, target: string, overwrite: bool, randomize: bool}>
     */
    protected $files = [];

    /**
     * Options argument may be either a string, a Laminas\Config\Config object, or an array.
     * If an array or Laminas\Config\Config object, it accepts the following keys:
     * 'source'    => Source filename or directory which will be renamed
     * 'target'    => Target filename or directory, the new name of the source file
     * 'overwrite' => Shall existing files be overwritten ?
     * 'randomize' => Shall target files have a random postfix attached?
     *
     * @param  string|array|Traversable $options Target file or directory to be renamed
     * @throws Exception\InvalidArgumentException
     */
    public function __construct($options = [])
    {
        if ($options instanceof Traversable) {
            $options = ArrayUtils::iteratorToArray($options);
        } elseif (is_string($options)) {
            $options = ['target' => $options];
        } elseif (! is_array($options)) {
            throw new Exception\InvalidArgumentException(
                'Invalid options argument provided to filter'
            );
        }

        $this->setFile($options);
    }

    /**
     * Returns the files to rename and their new name and location
     *
     * @return list<array{source: string, target: string, overwrite: bool, randomize: bool}>
     */
    public function getFile()
    {
        return $this->files;
    }

    /**
     * Sets a new file or directory as target, deleting existing ones
     *
     * Array accepts the following keys:
     * 'source'    => Source filename or directory which will be renamed
     * 'target'    => Target filename or directory, the new name of the sourcefile
     * 'overwrite' => Shall existing files be overwritten?
     * 'randomize' => Shall target files have a random postfix attached?
     *
     * @param  string|array{source?: string, target?: string, overwrite?: bool, randomize?: bool} $options
     * @return self
     */
    public function setFile($options)
    {
        $this->files = [];
        $this->addFile($options);

        return $this;
    }

    /**
     * Adds a new file or directory as target to the existing ones
     *
     * Array accepts the following keys:
     * 'source'    => Source filename or directory which will be renamed
     * 'target'    => Target filename or directory, the new name of the sourcefile
     * 'overwrite' => Shall existing files be overwritten?
     * 'randomize' => Shall target files have a random postfix attached?
     *
     * @param  string|array{source?: string, target?: string, overwrite?: bool, randomize?: bool} $options $options
     * @return Rename
     * @throws Exception\InvalidArgumentException
     */
    public function addFile($options)
    {
        if (is_string($options)) {
            $options = ['target' => $options];
        } elseif (! is_array($options)) {
            throw new Exception\InvalidArgumentException(
                'Invalid options to rename filter provided'
            );
        }

        $this->_convertOptions($options);

        return $this;
    }

    /**
     * Returns only the new filename without moving it
     * But existing files will be erased when the overwrite option is true
     *
     * @param  string  $value  Full path of file to change
     * @param  bool $source Return internal information
     * @return string The new filename which has been set
     * @throws Exception\InvalidArgumentException If the target file already exists.
     */
    public function getNewName($value, $source = false)
    {
        $file = $this->_getFileName($value);
        if (! is_array($file)) {
            return $file;
        }

        if ($file['source'] === $file['target']) {
            return $value;
        }

        if (! file_exists($file['source'])) {
            return $value;
        }

        if ($file['overwrite'] && file_exists($file['target'])) {
            unlink($file['target']);
        }

        if (file_exists($file['target'])) {
            throw new Exception\InvalidArgumentException(sprintf(
                '"File "%s" could not be renamed to "%s"; target file already exists',
                $value,
                realpath($file['target'])
            ));
        }

        if ($source) {
            return $file;
        }

        return $file['target'];
    }

    /**
     * Defined by Laminas\Filter\Filter
     *
     * Renames the file $value to the new name set before
     * Returns the file $value, removing all but digit characters
     *
     * @param mixed $value Full path of file to change or $_FILES data array
     * @return mixed|string|array The new filename which has been set
     * @throws Exception\RuntimeException
     */
    public function filter(mixed $value): mixed
    {
        if (! is_scalar($value) && ! is_array($value)) {
            return $value;
        }

        // An uploaded file? Retrieve the 'tmp_name'
        $isFileUpload = false;
        if (is_array($value)) {
            if (! isset($value['tmp_name'])) {
                return $value;
            }

            $isFileUpload = true;
            $uploadData   = $value;
            $value        = $value['tmp_name'];
        }

        $file = $this->getNewName((string) $value, true);
        if (is_string($file)) {
            if ($isFileUpload) {
                return $uploadData;
            } else {
                return $file;
            }
        }

        $result = rename($file['source'], $file['target']);

        if ($result !== true) {
            throw new Exception\RuntimeException(
                sprintf(
                    "File '%s' could not be renamed. "
                    . "An error occurred while processing the file.",
                    $value
                )
            );
        }

        if ($isFileUpload) {
            $uploadData['tmp_name'] = $file['target'];
            return $uploadData;
        }
        return $file['target'];
    }

    /**
     * Internal method for creating the file array
     * Supports single and nested arrays
     *
     * @param  array $options
     * @return $this
     */
    // @codingStandardsIgnoreStart
    protected function _convertOptions($options)
    {
        // @codingStandardsIgnoreEnd
        $files = [];
        foreach ($options as $key => $value) {
            if (is_array($value)) {
                $this->_convertOptions($value);
                continue;
            }

            switch ($key) {
                case "source":
                    $files['source'] = (string) $value;
                    break;

                case 'target':
                    $files['target'] = (string) $value;
                    break;

                case 'overwrite':
                    $files['overwrite'] = (bool) $value;
                    break;

                case 'randomize':
                    $files['randomize'] = (bool) $value;
                    break;

                default:
                    break;
            }
        }

        if ($files === []) {
            return $this;
        }

        if (! is_string($files['source'] ?? null)) {
            $files['source'] = '*';
        }

        if (! is_string($files['target'] ?? null)) {
            $files['target'] = '*';
        }

        if (! is_bool($files['overwrite'] ?? null)) {
            $files['overwrite'] = false;
        }

        if (! is_bool($files['randomize'] ?? null)) {
            $files['randomize'] = false;
        }

        $found = false;
        foreach ($this->files as $key => $value) {
            if ($value['source'] === $files['source']) {
                $this->files[$key] = $files;
                $found             = true;
            }
        }

        if (! $found) {
            $count               = count($this->files);
            $this->files[$count] = $files;
        }

        return $this;
    }

    /**
     * Internal method to resolve the requested source
     * and return all other related parameters
     *
     * @param  string $file Filename to get the information for
     * @return array|string
     */
    // @codingStandardsIgnoreStart
    protected function _getFileName($file)
    {
        // @codingStandardsIgnoreEnd
        $rename = [];
        foreach ($this->files as $value) {
            if ($value['source'] === '*') {
                if (! isset($rename['source'])) {
                    $rename           = $value;
                    $rename['source'] = $file;
                }
            }

            if ($value['source'] === $file) {
                $rename = $value;
                break;
            }
        }

        if (! isset($rename['source'])) {
            return $file;
        }

        if (! isset($rename['target']) || $rename['target'] === '*') {
            $rename['target'] = $rename['source'];
        }

        if (is_dir($rename['target'])) {
            $name = basename($rename['source']);
            $last = $rename['target'][strlen($rename['target']) - 1];
            if ($last !== '/' && $last !== '\\') {
                $rename['target'] .= DIRECTORY_SEPARATOR;
            }

            $rename['target'] .= $name;
        }

        if ($rename['randomize']) {
            $info      = pathinfo($rename['target']);
            $newTarget = $info['dirname'] . DIRECTORY_SEPARATOR
                . $info['filename'] . uniqid('_', false);
            if (isset($info['extension'])) {
                $newTarget .= '.' . $info['extension'];
            }
            $rename['target'] = $newTarget;
        }

        return $rename;
    }
}
