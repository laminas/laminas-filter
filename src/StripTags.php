<?php

declare(strict_types=1);

namespace Laminas\Filter;

use function array_change_key_case;
use function array_combine;
use function array_fill;
use function array_is_list;
use function array_map;
use function array_merge;
use function count;
use function in_array;
use function is_scalar;
use function preg_match;
use function preg_match_all;
use function sprintf;
use function str_contains;
use function str_replace;
use function strlen;
use function strpos;
use function strtolower;
use function substr;
use function trim;

use const CASE_LOWER;

/**
 * @psalm-type Options = array{
 *     allowTags?: list<string>|array<string, list<string>>,
 *     allowAttribs?: list<string>
 * }
 * @implements FilterInterface<string>
 */
final class StripTags implements FilterInterface
{
    /**
     * Array of allowed tags and allowed attributes for each allowed tag
     *
     * Tags are stored in the array keys, and the array values are themselves
     * arrays of the attributes allowed for the corresponding tag.
     *
     * @var array<string, list<string>>
     */
    private readonly array $tagsAllowed;

    /**
     * Array of allowed attributes for all allowed tags
     *
     * Attributes stored here are allowed for all of the allowed tags.
     *
     * @var list<string>
     */
    private readonly array $attributesAllowed;

    /**
     * @param Options $options
     */
    public function __construct(array $options = [])
    {
        $this->attributesAllowed = array_map(
            static fn (string $attribute): string => strtolower($attribute),
            $options['allowAttribs'] ?? [],
        );

        $tagsAllowed = $options['allowTags'] ?? [];

        if (array_is_list($tagsAllowed)) {
            /** @psalm-var list<string> $tagsAllowed */
            $tags = array_map(
                static fn (string $tag): string => strtolower($tag),
                $tagsAllowed,
            );

            $this->tagsAllowed = array_combine($tags, array_fill(0, count($tags), []));

            return;
        }

        /** @psalm-var array<string, list<string>> $tagsAllowed */
        $this->tagsAllowed = array_map(
            static fn (array $attributes) => array_map(
                static fn (string $attribute): string => strtolower($attribute),
                $attributes,
            ),
            array_change_key_case($tagsAllowed, CASE_LOWER),
        );
    }

    /**
     * Defined by Laminas\Filter\FilterInterface
     *
     * If the value provided is non-scalar, the value will remain unfiltered
     */
    public function filter(mixed $value): mixed
    {
        if (! is_scalar($value)) {
            return $value;
        }
        $value = (string) $value;

        // Strip HTML comments first
        $open     = '<!--';
        $openLen  = strlen($open);
        $close    = '-->';
        $closeLen = strlen($close);
        while (($start = strpos($value, $open)) !== false) {
            $end = strpos($value, $close, $start + $openLen);

            if ($end === false) {
                $value = substr($value, 0, $start);
            } else {
                $value = substr($value, 0, $start) . substr($value, $end + $closeLen);
            }
        }

        // Initialize accumulator for filtered data
        $dataFiltered = '';
        // Parse the input data iteratively as regular pre-tag text followed by a
        // tag; either may be empty strings
        preg_match_all('/([^<]*)(<?[^>]*>?)/', $value, $matches);

        // Iterate over each set of matches
        foreach ($matches[1] as $index => $preTag) {
            // If the pre-tag text is non-empty, strip any ">" characters from it
            if (strlen($preTag)) {
                $preTag = str_replace('>', '', $preTag);
            }
            // If a tag exists in this match, then filter the tag
            $tag = $matches[2][$index];
            if (strlen($tag)) {
                $tagFiltered = $this->filterTag($tag);
            } else {
                $tagFiltered = '';
            }
            // Add the filtered pre-tag text and filtered tag to the data buffer
            $dataFiltered .= $preTag . $tagFiltered;
        }

        // Return the filtered data
        return $dataFiltered;
    }

    public function __invoke(mixed $value): mixed
    {
        return $this->filter($value);
    }

    /**
     * Filters a single tag against the current option settings
     */
    private function filterTag(string $tag): string
    {
        // Parse the tag into:
        // 1. a starting delimiter (mandatory)
        // 2. a tag name (if available)
        // 3. a string of attributes (if available)
        // 4. an ending delimiter (if available)
        $isMatch = preg_match('~(</?)(\w*)((/(?!>)|[^/>])*)(/?>)~', $tag, $matches);

        // If the tag does not match, then strip the tag entirely
        if (! $isMatch) {
            return '';
        }

        // Save the matches to more meaningfully named variables
        $tagStart      = $matches[1];
        $tagName       = strtolower($matches[2]);
        $tagAttributes = $matches[3];
        $tagEnd        = $matches[5];

        // If the tag is not an allowed tag, then remove the tag entirely
        if (! isset($this->tagsAllowed[$tagName])) {
            return '';
        }

        $allowedAttributes = array_merge(
            $this->attributesAllowed,
            $this->tagsAllowed[$tagName],
        );

        // Trim the attribute string of whitespace at the ends
        $tagAttributes = trim($tagAttributes);

        // If there are non-whitespace characters in the attribute string
        if (strlen($tagAttributes)) {
            // Parse iteratively for well-formed attributes
            preg_match_all('/([\w-]+)\s*=\s*(?:(")(.*?)"|(\')(.*?)\')/s', $tagAttributes, $matches);

            // Initialize valid attribute accumulator
            $tagAttributes = '';

            // Iterate over each matched attribute
            foreach ($matches[1] as $index => $attributeName) {
                $attributeName      = strtolower($attributeName);
                $attributeDelimiter = $matches[2][$index] === '' ? $matches[4][$index] : $matches[2][$index];
                $attributeValue     = $matches[3][$index] === '' ? $matches[5][$index] : $matches[3][$index];

                // If the attribute is not allowed, then remove it entirely
                if (! in_array($attributeName, $allowedAttributes, true)) {
                    continue;
                }

                // Add the attribute to the accumulator
                $tagAttributes .= sprintf(
                    ' %s=%s%s%s',
                    $attributeName,
                    $attributeDelimiter,
                    $attributeValue,
                    $attributeDelimiter,
                );
            }
        }

        // Reconstruct tags ending with "/>" as backwards-compatible XHTML tag
        if (str_contains($tagEnd, '/')) {
            $tagEnd = " $tagEnd";
        }

        // Return the filtered tag
        return $tagStart . $tagName . $tagAttributes . $tagEnd;
    }
}
