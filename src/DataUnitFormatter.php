<?php

declare(strict_types=1);

namespace Laminas\Filter;

use Laminas\Filter\Exception\InvalidArgumentException;

use function floor;
use function in_array;
use function is_numeric;
use function log;
use function number_format;
use function sprintf;
use function strtolower;

/**
 * @psalm-type Options = array{
 *     mode?: string,
 *     unit?: string,
 *     precision?: int,
 *     prefixes?: list<string>,
 * }
 * @extends AbstractFilter<Options>
 * @final
 */
final class DataUnitFormatter extends AbstractFilter
{
    public const MODE_BINARY  = 'binary';
    public const MODE_DECIMAL = 'decimal';

    public const BASE_BINARY  = 1024;
    public const BASE_DECIMAL = 1000;

    private const DEFAULT_PRECISION = 2;
    /**
     * A list of all possible filter modes:
     *
     * @var list<string>
     */
    private static array $modes = [
        self::MODE_BINARY,
        self::MODE_DECIMAL,
    ];

    /**
     * A list of standardized binary prefix formats for decimal and binary mode
     *
     * @link https://en.wikipedia.org/wiki/Binary_prefix
     *
     * @var array<string, list<string>>
     */
    private static array $standardizedPrefixes = [
        // binary IEC units:
        self::MODE_BINARY => ['', 'Ki', 'Mi', 'Gi', 'Ti', 'Pi', 'Ei', 'Zi', 'Yi'],
        // decimal SI units:
        self::MODE_DECIMAL => ['', 'k', 'M', 'G', 'T', 'P', 'E', 'Z', 'Y'],
    ];

    /**
     * Default options:
     *
     * @var Options
     */
    protected $options = [
        'mode'      => self::MODE_DECIMAL,
        'unit'      => '',
        'precision' => self::DEFAULT_PRECISION,
        'prefixes'  => [],
    ];

    /**
     * @param Options $options
     */
    public function __construct($options = [])
    {
        if (! self::isOptions($options)) {
            throw new InvalidArgumentException('The unit filter needs options to work.');
        }

        if (! isset($options['unit'])) {
            throw new InvalidArgumentException('The unit filter needs a unit to work with.');
        }

        $this->setOptions($options);
    }

    /**
     * Define the mode of the filter. Possible values can be fount at self::$modes.
     *
     * @param string $mode
     * @throws InvalidArgumentException
     */
    protected function setMode($mode)
    {
        $mode = strtolower($mode);
        if (! in_array($mode, self::$modes, true)) {
            throw new InvalidArgumentException(sprintf('Invalid binary mode: %s', $mode));
        }
        $this->options['mode'] = $mode;
    }

    /**
     * Get current filter mode
     *
     * @return string
     */
    protected function getMode()
    {
        return $this->options['mode'] ?? self::MODE_DECIMAL;
    }

    /**
     * Find out if the filter is in decimal mode.
     *
     * @return bool
     */
    protected function isDecimalMode()
    {
        return $this->getMode() === self::MODE_DECIMAL;
    }

    /**
     * Find out if the filter is in binary mode.
     *
     * @return bool
     */
    protected function isBinaryMode()
    {
        return $this->getMode() === self::MODE_BINARY;
    }

    /**
     * Define the unit of the filter. Possible values can be fount at self::$types.
     *
     * @param string $unit
     */
    protected function setUnit($unit)
    {
        $this->options['unit'] = (string) $unit;
    }

    /**
     * Get current filter type
     *
     * @return string
     */
    protected function getUnit()
    {
        return $this->options['unit'] ?? '';
    }

    /**
     * Set the precision of the filtered result.
     *
     * @param int $precision
     */
    protected function setPrecision($precision)
    {
        $this->options['precision'] = (int) $precision;
    }

    /**
     * Get the precision of the filtered result.
     *
     * @return int
     */
    protected function getPrecision()
    {
        return $this->options['precision'] ?? self::DEFAULT_PRECISION;
    }

    /**
     * Set the precision of the result.
     *
     * @param list<string> $prefixes
     */
    protected function setPrefixes(array $prefixes)
    {
        $this->options['prefixes'] = $prefixes;
    }

    /**
     * Get the predefined prefixes or use the build-in standardized lists of prefixes.
     *
     * @return list<string>
     */
    protected function getPrefixes()
    {
        $prefixes = $this->options['prefixes'] ?? null;
        if ($prefixes !== null && $prefixes !== []) {
            return $prefixes;
        }

        return self::$standardizedPrefixes[$this->getMode()];
    }

    /**
     * Find the prefix at a specific location in the prefixes array.
     *
     * @return string|null
     */
    protected function getPrefixAt(mixed $index)
    {
        $prefixes = $this->getPrefixes();
        return $prefixes[$index] ?? null;
    }

    /**
     * Defined by Laminas\Filter\FilterInterface
     *
     * Returns a human readable format of the amount of bits or bytes.
     *
     * If the value provided is not numeric, the value will remain unfiltered
     *
     * @param  mixed $value
     * @return string|mixed
     * @psalm-return ($value is numeric ? string : mixed)
     */
    public function filter($value)
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
        $base   = $this->isBinaryMode() ? self::BASE_BINARY : self::BASE_DECIMAL;
        $power  = floor(log($amount, $base));
        $prefix = $this->getPrefixAt((int) $power);

        // When the amount is too big, no prefix can be found:
        if ($prefix === null) {
            return $this->formatAmount($amount);
        }

        // return formatted value:
        $result    = $amount / $base ** $power;
        $formatted = number_format($result, $this->getPrecision());
        return $this->formatAmount($formatted, $prefix);
    }

    /**
     * @param float|string $amount
     * @param string|null  $prefix
     * @return string
     */
    protected function formatAmount($amount, $prefix = null)
    {
        return sprintf('%s %s%s', (string) $amount, (string) $prefix, $this->getUnit());
    }
}
