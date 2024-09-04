<?php

declare(strict_types=1);

namespace Laminas\Filter;

use function is_string;
use function preg_replace;

/**
 * @psalm-type Options = array{
 *     charlist?: string|null,
 * }
 * @implements FilterInterface<string>
 */
final class StringTrim implements FilterInterface
{
    private readonly string $charlist;

    /** @param Options $options */
    public function __construct(array $options = [])
    {
        $list           = $options['charlist'] ?? '\\\\s';
        $this->charlist = $list === '' ? '\\\\s' : $list;
    }

    /**
     * Returns the string $value with characters stripped from the beginning and end
     *
     * @inheritDoc
     */
    public function filter(mixed $value): mixed
    {
        if (! is_string($value)) {
            return $value;
        }

        return $this->unicodeTrim($value);
    }

    /**
     * Unicode aware trim method
     */
    private function unicodeTrim(string $value): string
    {
        $chars = preg_replace(
            ['/[\^\-\]\\\]/S', '/\\\{4}/S', '/\//'],
            ['\\\\\\0', '\\', '\/'],
            $this->charlist,
        );

        $pattern = '/^[' . $chars . ']+|[' . $chars . ']+$/usSD';

        return preg_replace($pattern, '', $value);
    }

    public function __invoke(mixed $value): mixed
    {
        return $this->filter($value);
    }
}
