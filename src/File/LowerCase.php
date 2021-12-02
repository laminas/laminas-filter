<?php

declare(strict_types=1);

namespace Laminas\Filter\File;

use Laminas\Filter\Exception;
use Laminas\Filter\StringToLower;

use function file_exists;
use function file_get_contents;
use function file_put_contents;
use function is_array;
use function is_scalar;
use function is_writable;

class LowerCase extends StringToLower
{
    /**
     * Defined by Laminas\Filter\Filter
     *
     * Does a lowercase on the content of the given file
     *
     * @param  string|array $value Full path of file to change or $_FILES data array
     * @return string|array The given $value
     * @throws Exception\InvalidArgumentException
     * @throws Exception\RuntimeException
     */
    public function filter($value)
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

        if (! file_exists($value)) {
            throw new Exception\InvalidArgumentException("File '$value' not found");
        }

        if (! is_writable($value)) {
            throw new Exception\RuntimeException("File '$value' is not writable");
        }

        $content = file_get_contents($value);
        if (! $content) {
            throw new Exception\RuntimeException("Problem while reading file '$value'");
        }

        $content = parent::filter($content);
        $result  = file_put_contents($value, $content);

        if (! $result) {
            throw new Exception\RuntimeException("Problem while writing file '$value'");
        }

        if ($isFileUpload) {
            return $uploadData;
        }
        return $value;
    }
}
