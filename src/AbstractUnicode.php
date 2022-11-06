<?php

declare(strict_types=1);

namespace Laminas\Filter;

use function array_map;
use function assert;
use function in_array;
use function is_string;
use function mb_internal_encoding;
use function mb_list_encodings;
use function sprintf;
use function strtolower;

/**
 * @psalm-type UnicodeOptions = array{
 *     encoding?: string|null,
 * }
 * @template TOptions of UnicodeOptions
 * @extends AbstractFilter<UnicodeOptions>
 */
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
        $encoding = $this->options['encoding'] ?? null;
        assert($encoding === null || is_string($encoding));
        if ($encoding === null) {
            $encoding = mb_internal_encoding();
            assert(is_string($encoding));
            $this->options['encoding'] = $encoding;
        }

        return $encoding;
    }
}
