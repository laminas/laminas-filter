<?php

declare(strict_types=1);

namespace Laminas\Filter\File;

use Laminas\Filter\EncodingOption;
use Laminas\Filter\Exception\InvalidArgumentException;
use Laminas\Filter\Exception\RuntimeException;
use Laminas\Filter\FilterInterface;
use Laminas\Filter\StringToLower;

use function is_array;
use function is_string;

/**
 * @psalm-type Options = array{encoding?: string}
 * @implements FilterInterface<mixed>
 */
final class LowerCase implements FilterInterface
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
     * Lowercases the contents of the given file path
     *
     * @param mixed $value Full path of file to change or $_FILES data array
     * @throws InvalidArgumentException
     * @throws RuntimeException
     */
    public function filter(mixed $value): mixed
    {
        $filePath = null;

        if (is_string($value)) {
            $filePath = $value;
        }

        // An uploaded file? Retrieve the 'tmp_name'
        if (is_array($value)) {
            if (! isset($value['tmp_name']) || ! is_string($value['tmp_name'])) {
                return $value;
            }

            $filePath = $value['tmp_name'];
        }

        if ($filePath === null) {
            return $value;
        }

        (new FilterFileContents(
            new StringToLower(['encoding' => $this->encoding]),
        ))($filePath);

        return $value;
    }

    /** @inheritDoc */
    public function __invoke(mixed $value): mixed
    {
        return $this->filter($value);
    }
}
