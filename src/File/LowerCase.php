<?php

/**
 * @see       https://github.com/laminas/laminas-filter for the canonical source repository
 * @copyright https://github.com/laminas/laminas-filter/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-filter/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\Filter\File;

use Laminas\Filter\Exception;
use Laminas\Filter\StringToLower;

/**
 * @category   Laminas
 * @package    Laminas_Filter
 */
class LowerCase extends StringToLower
{
    /**
     * Defined by Laminas\Filter\Filter
     *
     * Does a lowercase on the content of the given file
     *
     * @param  string $value Full path of file to change
     * @return string The given $value
     * @throws Exception\InvalidArgumentException
     * @throws Exception\RuntimeException
     */
    public function filter($value)
    {
        if (!file_exists($value)) {
            throw new Exception\InvalidArgumentException("File '$value' not found");
        }

        if (!is_writable($value)) {
            throw new Exception\RuntimeException("File '$value' is not writable");
        }

        $content = file_get_contents($value);
        if (!$content) {
            throw new Exception\RuntimeException("Problem while reading file '$value'");
        }

        $content = parent::filter($content);
        $result  = file_put_contents($value, $content);

        if (!$result) {
            throw new Exception\RuntimeException("Problem while writing file '$value'");
        }

        return $value;
    }
}
