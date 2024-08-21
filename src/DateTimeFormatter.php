<?php

declare(strict_types=1);

namespace Laminas\Filter;

use DateTimeImmutable;
use DateTimeInterface;
use DateTimeZone;
use Exception;
use Laminas\Filter\Exception\InvalidArgumentException;
use Throwable;

use function date_default_timezone_get;
use function is_int;

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
     * @throws Exception
     */
    public function __construct(array $options = [])
    {
        $this->format   = $options['format'] ?? DateTimeInterface::ATOM;
        $this->timezone = new DateTimeZone($options['timezone'] ?? date_default_timezone_get());
    }

    /**
     * Filter a datetime string by normalizing it to the filters specified format
     *
     * @inheritDoc
     */
    public function filter(mixed $value): mixed
    {
        try {
            if (! $value instanceof DateTimeInterface) {
                $value = is_int($value) ? '@' . (string)$value : $value;
                $value = new DateTimeImmutable($value, $this->timezone);
            } else {
                $value = new DateTimeImmutable($value->format($this->format), $this->timezone);
            }
        } catch (Throwable $e) {
            throw new InvalidArgumentException('Invalid date string provided', $e->getCode(), $e);
        }

        return $value->format($this->format);
    }

    public function __invoke(mixed $value): mixed
    {
        return $this->filter($value);
    }
}
