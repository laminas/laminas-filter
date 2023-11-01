<?php

declare(strict_types=1);

namespace Laminas\Filter;

use Traversable;

use function array_search;
use function gettype;
use function is_array;
use function is_bool;
use function is_float;
use function is_int;
use function is_string;
use function iterator_to_array;
use function sprintf;

/**
 * @psalm-type Options = array{
 *     type?: int-mask-of<self::TYPE_*>,
 * }
 * @extends AbstractFilter<Options>
 * @final
 */
class ToNull extends AbstractFilter
{
    public const TYPE_BOOLEAN     = 1;
    public const TYPE_INTEGER     = 2;
    public const TYPE_EMPTY_ARRAY = 4;
    public const TYPE_STRING      = 8;
    public const TYPE_ZERO_STRING = 16;
    public const TYPE_FLOAT       = 32;
    public const TYPE_ALL         = 63;

    /**
     * @deprecated since 2.26 - superseded by self::CONSTANTS
     *
     * @var array<self::TYPE_*, string>
     */
    protected $constants = self::CONSTANTS;

    private const CONSTANTS = [
        self::TYPE_BOOLEAN     => 'boolean',
        self::TYPE_INTEGER     => 'integer',
        self::TYPE_EMPTY_ARRAY => 'array',
        self::TYPE_STRING      => 'string',
        self::TYPE_ZERO_STRING => 'zero',
        self::TYPE_FLOAT       => 'float',
        self::TYPE_ALL         => 'all',
    ];

    /** @var Options */
    protected $options = [
        'type' => self::TYPE_ALL,
    ];

    /**
     * @phpcs:disable Generic.Files.LineLength.TooLong
     * @param int-mask-of<self::TYPE_*>|value-of<self::CONSTANTS>|list<self::TYPE_*>|Options|iterable|null $typeOrOptions
     */
    public function __construct($typeOrOptions = null)
    {
        if ($typeOrOptions === null || $typeOrOptions === '') {
            return;
        }

        if ($typeOrOptions instanceof Traversable) {
            $typeOrOptions = iterator_to_array($typeOrOptions);
        }

        if (is_array($typeOrOptions) && isset($typeOrOptions['type'])) {
            $this->setOptions($typeOrOptions);

            return;
        }

        $this->setType($typeOrOptions);
    }

    /**
     * Set boolean types
     *
     * @param int-mask-of<self::TYPE_*>|value-of<self::CONSTANTS>|list<self::TYPE_*>|null $type
     * @throws Exception\InvalidArgumentException
     * @return self
     */
    public function setType($type = null)
    {
        if (is_array($type)) {
            $detected = 0;
            foreach ($type as $value) {
                if (is_int($value)) {
                    $detected |= $value;
                } elseif (($found = array_search($value, self::CONSTANTS, true)) !== false) {
                    $detected |= $found;
                }
            }

            $type = $detected;
        } elseif (is_string($type) && ($found = array_search($type, self::CONSTANTS, true)) !== false) {
            $type = $found;
        }

        if (! is_int($type) || ($type < 0) || ($type > self::TYPE_ALL)) {
            throw new Exception\InvalidArgumentException(sprintf(
                'Unknown type value "%s" (%s)',
                $type,
                gettype($type)
            ));
        }

        $this->options['type'] = $type;
        return $this;
    }

    /**
     * Returns defined boolean types
     *
     * @return int-mask-of<self::TYPE_*>
     */
    public function getType()
    {
        return $this->options['type'];
    }

    /**
     * Defined by Laminas\Filter\FilterInterface
     *
     * Returns null representation of $value, if value is empty and matches
     * types that should be considered null.
     *
     * @param  null|array|bool|float|int|string $value
     * @return null|mixed
     */
    public function filter($value)
    {
        $type = $this->getType();

        // FLOAT (0.0)
        if ($type & self::TYPE_FLOAT) {
            if (is_float($value) && $value === 0.0) {
                return null;
            }
        }

        // STRING ZERO ('0')
        if ($type & self::TYPE_ZERO_STRING) {
            if (is_string($value) && $value === '0') {
                return null;
            }
        }

        // STRING ('')
        if ($type & self::TYPE_STRING) {
            if (is_string($value) && $value === '') {
                return null;
            }
        }

        // EMPTY_ARRAY (array())
        if ($type & self::TYPE_EMPTY_ARRAY) {
            if (is_array($value) && $value === []) {
                return null;
            }
        }

        // INTEGER (0)
        if ($type & self::TYPE_INTEGER) {
            if (is_int($value) && $value === 0) {
                return null;
            }
        }

        // BOOLEAN (false)
        if ($type & self::TYPE_BOOLEAN) {
            if (is_bool($value) && $value === false) {
                return null;
            }
        }

        return $value;
    }
}
