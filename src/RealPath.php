<?php

declare(strict_types=1);

namespace Laminas\Filter;

use Laminas\Stdlib\ErrorHandler;
use Traversable;

use function array_pop;
use function explode;
use function getcwd;
use function implode;
use function is_string;
use function preg_match;
use function preg_replace;
use function realpath;
use function str_starts_with;
use function stripos;
use function substr;

use const DIRECTORY_SEPARATOR;
use const PHP_OS;

/**
 * @psalm-type Options = array{
 *     exists: bool,
 *     ...
 * }
 * @template TOptions of Options
 * @extends AbstractFilter<TOptions>
 */
final class RealPath extends AbstractFilter
{
    /** @var TOptions $options */
    protected $options = [
        'exists' => true,
    ];

    /**
     * @param  bool|Traversable|Options $existsOrOptions Options to set
     */
    public function __construct($existsOrOptions = true)
    {
        if ($existsOrOptions !== null) {
            if (! static::isOptions($existsOrOptions)) {
                $this->setExists($existsOrOptions);
            } else {
                $this->setOptions($existsOrOptions);
            }
        }
    }

    /**
     * Sets if the path has to exist
     * TRUE when the path must exist
     * FALSE when not existing paths can be given
     *
     * @param  bool $flag Path must exist
     * @return self
     */
    public function setExists($flag = true)
    {
        $this->options['exists'] = (bool) $flag;
        return $this;
    }

    /**
     * Returns true if the filtered path must exist
     *
     * @return bool
     */
    public function getExists()
    {
        return $this->options['exists'];
    }

    /**
     * Defined by Laminas\Filter\FilterInterface
     *
     * Returns realpath($value)
     *
     * If the value provided is non-scalar, the value will remain unfiltered
     *
     * @psalm-return ($value is string ? string : mixed)
     */
    public function filter(mixed $value): mixed
    {
        if (! is_string($value)) {
            return $value;
        }
        $path = (string) $value;

        if ($this->options['exists']) {
            return realpath($path);
        }

        ErrorHandler::start();
        $realpath = realpath($path);
        ErrorHandler::stop();
        if ($realpath !== false) {
            return $realpath;
        }

        $drive = '';
        if (stripos(PHP_OS, 'WIN') === 0) {
            $path = preg_replace('/[\\\\\/]/', DIRECTORY_SEPARATOR, $path);
            if (preg_match('/([a-zA-Z]\:)(.*)/', $path, $matches)) {
                [, $drive, $path] = $matches;
            } else {
                $cwd   = getcwd();
                $drive = substr($cwd, 0, 2);
                if (! str_starts_with($path, DIRECTORY_SEPARATOR)) {
                    $path = substr($cwd, 3) . DIRECTORY_SEPARATOR . $path;
                }
            }
        } elseif (! str_starts_with($path, DIRECTORY_SEPARATOR)) {
            $path = getcwd() . DIRECTORY_SEPARATOR . $path;
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

        return $drive . DIRECTORY_SEPARATOR . implode(DIRECTORY_SEPARATOR, $stack);
    }
}
