<?php

declare(strict_types=1);

namespace Laminas\Filter;

use function array_map;
use function function_exists;
use function in_array;
use function mb_internal_encoding;
use function mb_list_encodings;
use function sprintf;
use function strtolower;

abstract class AbstractUnicode extends AbstractFilter
{
    /**
     * Set the input encoding for the given string
     *
     * @param  string|null $encoding
     * @return self
     * @throws Exception\InvalidArgumentException
     * @throws Exception\ExtensionNotLoadedException
     */
    public function setEncoding($encoding = null)
    {
        if ($encoding !== null) {
            if (! function_exists('mb_strtolower')) {
                throw new Exception\ExtensionNotLoadedException(sprintf(
                    '%s requires mbstring extension to be loaded',
                    static::class
                ));
            }

            $encoding    = strtolower($encoding);
            $mbEncodings = array_map('strtolower', mb_list_encodings());
            if (! in_array($encoding, $mbEncodings, true)) {
                throw new Exception\InvalidArgumentException(sprintf(
                    "Encoding '%s' is not supported by mbstring extension",
                    $encoding
                ));
            }
        }

        $this->options['encoding'] = $encoding;
        return $this;
    }

    /**
     * Returns the set encoding
     *
     * @return string
     */
    public function getEncoding()
    {
        if ($this->options['encoding'] === null && function_exists('mb_internal_encoding')) {
            $this->options['encoding'] = mb_internal_encoding();
        }

        return $this->options['encoding'];
    }
}
