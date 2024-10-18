<?php

declare(strict_types=1);

namespace Laminas\Filter;

use Laminas\Filter\Exception\InvalidArgumentException;

use function floor;
use function is_numeric;
use function log;
use function number_format;
use function sprintf;

/**
 * @psalm-type Options = array{
 *     mode?: DataUnitFormatter::MODE_BINARY|DataUnitFormatter::MODE_DECIMAL,
 *     precision?: positive-int,
 * }
 * @implements FilterInterface<string>
 */
final class DataUnitFormatter implements FilterInterface
{
    public const MODE_BINARY  = 'binary';
    public const MODE_DECIMAL = 'decimal';

    private const BASE_BINARY       = 1024;
    private const BASE_DECIMAL      = 1000;
    private const DEFAULT_PRECISION = 2;

    /**
     * A list of standardized binary prefix formats for decimal and binary mode
     *
     * @link https://en.wikipedia.org/wiki/Binary_prefix
     *
     * @var array<string, list<string>>
     */
    private const STANDARD_PREFIXES = [
        // binary IEC units:
        self::MODE_BINARY => ['', 'Ki', 'Mi', 'Gi', 'Ti', 'Pi', 'Ei', 'Zi', 'Yi'],
        // decimal SI units:
        self::MODE_DECIMAL => ['', 'k', 'M', 'G', 'T', 'P', 'E', 'Z', 'Y'],
    ];

    /** @var self::MODE_DECIMAL|self::MODE_BINARY */
    private readonly string $mode;
    /** @var positive-int */
    private readonly int $precision;

    /**
     * @param Options $options
     */
    public function __construct(array $options = [])
    {
        $mode = $options['mode'] ?? self::MODE_DECIMAL;
        /** @psalm-suppress DocblockTypeContradiction, NoValue - Defensive check */
        if ($mode !== self::MODE_BINARY && $mode !== self::MODE_DECIMAL) {
            throw new InvalidArgumentException(sprintf('Invalid binary mode: %s', $mode));
        }

        $this->mode      = $mode;
        $this->precision = $options['precision'] ?? self::DEFAULT_PRECISION;
    }

    /**
     * Returns a human-readable format of the amount of bits or bytes.
     *
     * If the value provided is not numeric, the value will remain unfiltered
     *
     * @inheritDoc
     */
    public function filter(mixed $value): mixed
    {
        if (! is_numeric($value)) {
            return $value;
        }

        // Parse to float and check if value is not zero
        $amount = (float) $value;
        if ($amount === 0.0) {
            return $this->formatAmount($amount);
        }

        // Calculate the correct size and prefix:
        $base   = $this->mode === self::MODE_BINARY ? self::BASE_BINARY : self::BASE_DECIMAL;
        $power  = floor(log($amount, $base));
        $prefix = self::STANDARD_PREFIXES[$this->mode][(int) $power] ?? null;

        // When the amount is too big, no prefix can be found:
        if ($prefix === null) {
            return $this->formatAmount($amount);
        }

        // return formatted value:
        $result    = $amount / $base ** $power;
        $formatted = number_format($result, $this->precision);

        return $this->formatAmount($formatted, $prefix);
    }

    private function formatAmount(string|float $amount, ?string $prefix = null): string
    {
        return sprintf('%s %sB', (string) $amount, (string) $prefix);
    }

    /** @inheritDoc */
    public function __invoke(mixed $value): mixed
    {
        return $this->filter($value);
    }
}
