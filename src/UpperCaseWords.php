<?php

declare(strict_types=1);

namespace Laminas\Filter;

use function is_string;
use function mb_convert_case;

use const MB_CASE_TITLE;

/**
 * @psalm-type Options = array{encoding?: string}
 * @implements FilterInterface<string>
 */
final class UpperCaseWords implements FilterInterface
{
    private readonly string $encoding;

    /**
     * @param Options $options
     */
    public function __construct(array $options = [])
    {
        $this->encoding = EncodingOption::assertWithDefault($options['encoding'] ?? null);
    }

    /**
     * Returns the string $value, converting words to have an uppercase first character as necessary
     *
     * If the value provided is not a string, the value will remain unfiltered
     */
    public function filter(mixed $value): mixed
    {
        if (! is_string($value)) {
            return $value;
        }

        return mb_convert_case($value, MB_CASE_TITLE, $this->encoding);
    }

    public function __invoke(mixed $value): mixed
    {
        return $this->filter($value);
    }
}
