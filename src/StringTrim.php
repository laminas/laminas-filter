<?php

declare(strict_types=1);

namespace Laminas\Filter;

use function assert;
use function is_string;
use function preg_replace;

/**
 * @psalm-type Options = array{
 *     charlist?: string|null,
 * }
 * @implements FilterInterface<string>
 */
final readonly class StringTrim implements FilterInterface
{
    private string $charlist;

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
        assert(is_string($chars));

        $pattern = '/^[' . $chars . ']+|[' . $chars . ']+$/usSD';

        $value = preg_replace($pattern, '', $value);
        assert(is_string($value));

        return $value;
    }

    public function __invoke(mixed $value): mixed
    {
        return $this->filter($value);
    }
}
