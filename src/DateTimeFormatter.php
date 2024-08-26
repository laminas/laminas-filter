<?php

declare(strict_types=1);

namespace Laminas\Filter;

use DateTimeImmutable;
use DateTimeInterface;
use DateTimeZone;
use Laminas\Filter\Exception\InvalidArgumentException;
use Throwable;

use function date_default_timezone_get;
use function is_int;
use function is_string;

/**
 * @psalm-type Options = array{
 *      format?: non-empty-string,
 *      timezone?: non-empty-string,
 * }
 * @implements FilterInterface<string>
 */
final class DateTimeFormatter implements FilterInterface
{
    /**
     * A valid format string accepted by date()
     */
    private readonly string $format;

    /**
     * A valid timezone string
     */
    private readonly DateTimeZone $timezone;

    /**
     * @param Options $options
     */
    public function __construct(array $options = [])
    {
        $this->format = $options['format'] ?? DateTimeInterface::ATOM;

        try {
            $this->timezone = new DateTimeZone(
                $options['timezone'] ?? date_default_timezone_get()
            );
        } catch (Throwable $e) {
            throw new InvalidArgumentException('Invalid timezone provided');
        }
    }

    /**
     * Filter a datetime string by normalizing it to the filters specified format
     *
     * @inheritDoc
     */
    public function filter(mixed $value): mixed
    {
        if (
            ! (is_string($value) && $value !== '')
            && ! is_int($value)
            && ! $value instanceof DateTimeInterface
        ) {
            return $value;
        }

        try {
            if (is_int($value)) {
                $value = '@' . (string) $value;
            }

            if (is_string($value)) {
                $value = new DateTimeImmutable($value, $this->timezone);
            }
        } catch (Throwable $e) {
            throw new InvalidArgumentException('Invalid date/time string provided');
        }

        return $value->format($this->format);
    }

    public function __invoke(mixed $value): mixed
    {
        return $this->filter($value);
    }
}
