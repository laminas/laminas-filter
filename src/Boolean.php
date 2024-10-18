<?php

declare(strict_types=1);

namespace Laminas\Filter;

use function array_merge;
use function array_search;
use function assert;
use function gettype;
use function in_array;
use function is_array;
use function is_bool;
use function is_int;
use function is_string;
use function sprintf;
use function strtolower;

/**
 * @psalm-immutable
 * phpcs:disable Generic.Files.LineLength
 * @psalm-type TypeOption = int-mask-of<self::TYPE_*>|list<self::TYPE_*>|list<value-of<self::CONSTANTS>>|value-of<self::CONSTANTS>
 * @psalm-type OptionsArgument = array{
 *     type?: TypeOption,
 *     casting?: bool,
 *     translations?: array<string, bool>,
 * }
 * @psalm-type Options = array{
 *     type: int-mask-of<self::TYPE_*>,
 *     casting: bool,
 *     translations: array<string, bool>,
 * }
 * @implements FilterInterface<bool>
 */
final class Boolean implements FilterInterface
{
    public const TYPE_BOOLEAN      = 1;
    public const TYPE_INTEGER      = 2;
    public const TYPE_FLOAT        = 4;
    public const TYPE_STRING       = 8;
    public const TYPE_ZERO_STRING  = 16;
    public const TYPE_EMPTY_ARRAY  = 32;
    public const TYPE_NULL         = 64;
    public const TYPE_PHP          = 127;
    public const TYPE_FALSE_STRING = 128;
    public const TYPE_LOCALIZED    = 256;
    public const TYPE_ALL          = 511;

    private const CONSTANTS = [
        self::TYPE_BOOLEAN      => 'boolean',
        self::TYPE_INTEGER      => 'integer',
        self::TYPE_FLOAT        => 'float',
        self::TYPE_STRING       => 'string',
        self::TYPE_ZERO_STRING  => 'zero',
        self::TYPE_EMPTY_ARRAY  => 'array',
        self::TYPE_NULL         => 'null',
        self::TYPE_PHP          => 'php',
        self::TYPE_FALSE_STRING => 'false',
        self::TYPE_LOCALIZED    => 'localized',
        self::TYPE_ALL          => 'all',
    ];

    /** @var Options */
    private readonly array $options;

    /**
     * @param OptionsArgument $options
     */
    public function __construct(array $options = [])
    {
        $defaults = [
            'type'         => self::TYPE_PHP,
            'casting'      => true,
            'translations' => [],
        ];

        $options         = array_merge($defaults, $options);
        $options['type'] = $this->resolveType($options['type']);
        $this->options   = $options;
    }

    /**
     * Resolve int-mask type from various options
     *
     * @param int-mask-of<self::TYPE_*>|list<self::TYPE_*>|list<value-of<self::CONSTANTS>>|value-of<self::CONSTANTS> $type
     * @return int-mask-of<self::TYPE_*>
     * @throws Exception\InvalidArgumentException
     */
    private function resolveType(array|int|string $type): int
    {
        if (is_int($type) && ($type & self::TYPE_ALL) !== 0) {
            return $type;
        }

        if (is_string($type) && in_array($type, self::CONSTANTS, true)) {
            $type = array_search($type, self::CONSTANTS, true);
            assert(is_int($type));

            return $type;
        }

        if (is_array($type)) {
            $detected = 0;
            foreach ($type as $value) {
                if (is_int($value)) {
                    assert(($value & self::TYPE_ALL) !== 0);
                    $detected |= $value;
                } else {
                    $found = array_search($value, self::CONSTANTS, true);
                    assert(is_int($found));

                    $detected |= $found;
                }
            }

            /** @psalm-var int-mask-of<self::TYPE_*> */
            return $detected;
        }

        throw new Exception\InvalidArgumentException(sprintf(
            'Unknown type value "%s" (%s)',
            $type,
            gettype($type),
        ));
    }

    /**
     * Returns a boolean representation of $value
     */
    public function filter(mixed $value): mixed
    {
        $type    = $this->options['type'];
        $casting = $this->options['casting'];

        // LOCALIZED
        if ($type & self::TYPE_LOCALIZED) {
            if (is_string($value)) {
                if (isset($this->options['translations'][$value])) {
                    return $this->options['translations'][$value];
                }
            }
        }

        // FALSE_STRING ('false')
        if ($type & self::TYPE_FALSE_STRING) {
            if (is_string($value) && strtolower($value) === 'false') {
                return false;
            }

            if (is_string($value) && strtolower($value) === 'true') {
                return true;
            }
        }

        // NULL (null)
        if ($type & self::TYPE_NULL) {
            if ($value === null) {
                return false;
            }
        }

        // EMPTY_ARRAY (array())
        if ($type & self::TYPE_EMPTY_ARRAY) {
            if ($value === []) {
                return false;
            }
        }

        // ZERO_STRING ('0')
        if ($type & self::TYPE_ZERO_STRING) {
            if ($value === '0') {
                return false;
            }

            if (! $casting && $value === '1') {
                return true;
            }
        }

        // STRING ('')
        if ($type & self::TYPE_STRING) {
            if ($value === '') {
                return false;
            }
        }

        // FLOAT (0.0)
        if ($type & self::TYPE_FLOAT) {
            if ($value === 0.0) {
                return false;
            }

            if (! $casting && $value === 1.0) {
                return true;
            }
        }

        // INTEGER (0)
        if ($type & self::TYPE_INTEGER) {
            if ($value === 0) {
                return false;
            }

            if (! $casting && $value === 1) {
                return true;
            }
        }

        // BOOLEAN (false)
        if ($type & self::TYPE_BOOLEAN) {
            if (is_bool($value)) {
                return $value;
            }
        }

        if ($casting) {
            return true;
        }

        return $value;
    }

    public function __invoke(mixed $value): mixed
    {
        return $this->filter($value);
    }
}
