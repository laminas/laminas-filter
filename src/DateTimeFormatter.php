<?php

declare(strict_types=1);

namespace Laminas\Filter;

use DateTime;
use Throwable;
use Traversable;

use function is_int;
use function is_string;

/**
 * @psalm-type Options = array{
 *     format?: string,
 *     ...
 * }
 * @extends AbstractFilter<Options>
 * @final
 */
class DateTimeFormatter extends AbstractFilter
{
    /**
     * A valid format string accepted by date()
     *
     * @var string
     */
    protected $format = DateTime::ISO8601;

    /**
     * Sets filter options
     *
     * @param array|Traversable $options
     */
    public function __construct($options = null)
    {
        if ($options) {
            $this->setOptions($options);
        }
    }

    /**
     * Set the format string accepted by date() to use when formatting a string
     *
     * @param  string $format
     * @return self
     */
    public function setFormat($format)
    {
        $this->format = $format;

        return $this;
    }

    /**
     * Filter a datetime string by normalizing it to the filters specified format
     *
     * @param  DateTime|string|int|mixed $value
     * @throws Exception\InvalidArgumentException
     * @return string|mixed
     */
    public function filter($value)
    {
        try {
            $result = $this->normalizeDateTime($value);
        } catch (Throwable $e) {
            // DateTime threw an exception, an invalid date string was provided
            throw new Exception\InvalidArgumentException('Invalid date string provided', $e->getCode(), $e);
        }

        if ($result === false) {
            return $value;
        }

        return $result;
    }

    /**
     * Normalize the provided value to a formatted string
     *
     * @return string|mixed
     */
    protected function normalizeDateTime(mixed $value)
    {
        if ($value === '' || $value === null) {
            return $value;
        }

        if (! is_string($value) && ! is_int($value) && ! $value instanceof DateTime) {
            return $value;
        }

        if (is_int($value)) {
            //timestamp
            $value = new DateTime('@' . $value);
        } elseif (! $value instanceof DateTime) {
            $value = new DateTime($value);
        }

        return $value->format($this->format);
    }
}
