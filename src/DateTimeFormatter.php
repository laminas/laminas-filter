<?php

declare(strict_types=1);

namespace Laminas\Filter;

use DateTime;
use DateTimeInterface;
use Exception;
use Laminas\Filter\Exception\InvalidArgumentException;
use Throwable;

use function is_int;
use function is_string;

/**
 * @implements FilterInterface<mixed>
 */
final class DateTimeFormatter implements FilterInterface
{
    /**
     * A valid format string accepted by date()
     */
    protected string $format = DateTimeInterface::ATOM;

    /**
     * Set the format string accepted by date() to use when formatting a string
     */
    public function setFormat(string $format): DateTimeFormatter
    {
        $this->format = $format;

        return $this;
    }

    /**
     * Filter a datetime string by normalizing it to the filters specified format
     *
     * @inheritDoc
     */
    public function filter(mixed $value): mixed
    {
        try {
            $result = $this->normalizeDateTime($value);
        } catch (Throwable $e) {
            // DateTime threw an exception, an invalid date string was provided
            throw new InvalidArgumentException('Invalid date string provided', $e->getCode(), $e);
        }

        if ($result === false) {
            return $value;
        }

        return $result;
    }

    /**
     * Normalize the provided value to a formatted string
     *
     * @throws Exception
     */
    protected function normalizeDateTime(mixed $value): mixed
    {
        if ($value === '' || $value === null) {
            return $value;
        }

        if (! is_string($value) && ! is_int($value) && ! $value instanceof DateTimeInterface) {
            return $value;
        }

        if (is_int($value)) {
            //timestamp
            $value = new DateTime('@' . $value);
        } elseif (! $value instanceof DateTimeInterface) {
            $value = new DateTime($value);
        }

        return $value->format($this->format);
    }

    public function __invoke(mixed $value): mixed
    {
        return $this->filter($value);
    }
}
