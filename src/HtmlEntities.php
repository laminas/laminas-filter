<?php

declare(strict_types=1);

namespace Laminas\Filter;

use function assert;
use function function_exists;
use function htmlentities;
use function iconv;
use function is_string;
use function strlen;

use const ENT_QUOTES;

/**
 * @psalm-type Options = array{
 *     quotestyle?: int,
 *     encoding?: string,
 *     doublequote?: bool,
 * }
 * @implements FilterInterface<string>
 */
final class HtmlEntities implements FilterInterface
{
    /**
     * Corresponds to the second htmlentities() argument
     */
    private readonly int $quoteStyle;

    /**
     * Corresponds to the third htmlentities() argument
     */
    private readonly string $encoding;

    /**
     * Corresponds to the forth htmlentities() argument
     */
    private readonly bool $doubleQuote;

    /**
     * Sets filter options
     *
     * @param Options $options
     */
    public function __construct(array $options = [])
    {
        $this->quoteStyle  = $options['quotestyle'] ?? ENT_QUOTES;
        $this->encoding    = $options['encoding'] ?? 'UTF-8';
        $this->doubleQuote = $options['doublequote'] ?? true;
    }

    /**
     * Defined by Laminas\Filter\FilterInterface
     *
     * Returns the string $value, converting characters to their corresponding HTML entity
     * equivalents where they exist
     *
     * If the value provided is non-scalar, the value will remain unfiltered
     *
     * @throws Exception\DomainException On encoding mismatches.
     */
    public function filter(mixed $value): mixed
    {
        if (! is_string($value) || $value === '') {
            return $value;
        }

        $filtered = htmlentities($value, $this->quoteStyle, $this->encoding, $this->doubleQuote);
        if (strlen($filtered) === 0) {
            if (! function_exists('iconv')) {
                throw new Exception\DomainException('Encoding mismatch has resulted in htmlentities errors');
            }

            $value = iconv('', $this->encoding . '//IGNORE', $value);
            assert(is_string($value));
            $filtered = htmlentities($value, $this->quoteStyle, $this->encoding, $this->doubleQuote);
            if (strlen($filtered) === 0) {
                throw new Exception\DomainException('Encoding mismatch has resulted in htmlentities errors');
            }
        }
        return $filtered;
    }

    public function __invoke(mixed $value): mixed
    {
        return $this->filter($value);
    }
}
