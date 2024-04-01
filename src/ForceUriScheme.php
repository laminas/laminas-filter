<?php

declare(strict_types=1);

namespace Laminas\Filter;

use Laminas\Filter\Exception\InvalidArgumentException;

use function is_string;
use function ltrim;
use function parse_url;
use function preg_match;
use function preg_quote;
use function preg_replace;
use function sprintf;

/**
 * @psalm-type Options = array{scheme: non-empty-string}
 */
final class ForceUriScheme implements FilterInterface
{
    private const DEFAULT_SCHEME = 'https';

    /** @var non-empty-string */
    private string $scheme;

    /** @param Options $options */
    public function __construct(array $options = ['scheme' => self::DEFAULT_SCHEME])
    {
        if (! preg_match('/^[a-z0-9]+$/i', $options['scheme'])) {
            throw new InvalidArgumentException(sprintf(
                'The `scheme` option should be a string consisting only of letters and numbers. Please omit the :// '
                . ' Received "%s"',
                $options['scheme'],
            ));
        }

        $this->scheme = $options['scheme'];
    }

    public function __invoke(mixed $value): mixed
    {
        return $this->filter($value);
    }

    public function filter(mixed $value): mixed
    {
        if (! is_string($value) || $value === '') {
            return $value;
        }

        $url = parse_url($value);

        if (! isset($url['host']) || $url['host'] === '') {
            return $value;
        }

        if (! isset($url['scheme']) || $url['scheme'] === '') {
            return sprintf(
                '%s://%s',
                $this->scheme,
                ltrim($value, ':/'),
            );
        }

        $search  = sprintf(
            '/^(%s)(.+)/',
            preg_quote($url['scheme'], '/'),
        );
        $replace = sprintf(
            '%s$2',
            $this->scheme,
        );

        return preg_replace($search, $replace, $value);
    }
}
