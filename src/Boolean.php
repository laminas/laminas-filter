<?php

declare(strict_types=1);

namespace Laminas\Filter;

use Laminas\Stdlib\ArrayUtils;
use Traversable;

use function array_search;
use function get_debug_type;
use function gettype;
use function is_array;
use function is_bool;
use function is_float;
use function is_int;
use function is_string;
use function sprintf;
use function strtolower;

/**
 * @psalm-type Options = array{
 *     type?: int-mask-of<self::TYPE_*>,
 *     casting?: bool,
 *     translations?: array,
 * }
 * @extends AbstractFilter<Options>
 * @final
 */
class Boolean extends AbstractFilter
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

    /**
     * @deprecated since 2.26 - superseded by self::CONSTANTS
     *
     * @var array<self::TYPE_*, string>
     */
    protected $constants = self::CONSTANTS;

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
    protected $options = [
        'type'         => self::TYPE_PHP,
        'casting'      => true,
        'translations' => [],
    ];

    /**
     * phpcs:ignore Generic.Files.LineLength,SlevomatCodingStandard.Commenting.DocCommentSpacing
     * @param self::TYPE_*|value-of<self::CONSTANTS>|list<self::TYPE_*>|int-mask-of<self::TYPE_*>|Options|iterable|null $typeOrOptions
     * @param bool  $casting
     * @param array $translations
     */
    public function __construct($typeOrOptions = null, $casting = true, $translations = [])
    {
        if ($typeOrOptions instanceof Traversable) {
            $typeOrOptions = ArrayUtils::iteratorToArray($typeOrOptions);
        }

        if (
            is_array($typeOrOptions) && (
                isset($typeOrOptions['type'])
                || isset($typeOrOptions['casting'])
                || isset($typeOrOptions['translations'])
            )
        ) {
            $this->setOptions($typeOrOptions);

            return;
        }

        if (is_array($typeOrOptions) || is_int($typeOrOptions) || is_string($typeOrOptions)) {
            $this->setType($typeOrOptions);
        }

        $this->setCasting($casting);
        $this->setTranslations($translations);
    }

    /**
     * Set boolean types
     *
     * @deprecated since 2.37.0 - All option setters and getters will be removed in 3.0
     *
     * @param  self::TYPE_*|int-mask-of<self::TYPE_*>|value-of<self::CONSTANTS>|list<self::TYPE_*>|null $type
     * @return self
     * @throws Exception\InvalidArgumentException
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
     * @deprecated since 2.37.0 - All option setters and getters will be removed in 3.0
     *
     * @return int-mask-of<self::TYPE_*>
     */
    public function getType()
    {
        return $this->options['type'];
    }

    /**
     * Set the working mode
     *
     * @deprecated since 2.37.0 - All option setters and getters will be removed in 3.0
     *
     * @param  bool $flag When true this filter works like cast
     *                       When false it recognises only true and false
     *                       and all other values are returned as is
     * @return self
     */
    public function setCasting($flag = true)
    {
        $this->options['casting'] = (bool) $flag;
        return $this;
    }

    /**
     * Returns the casting option
     *
     * @deprecated since 2.37.0 - All option setters and getters will be removed in 3.0
     *
     * @return bool
     */
    public function getCasting()
    {
        return $this->options['casting'];
    }

    /**
     * @deprecated since 2.37.0 - All option setters and getters will be removed in 3.0
     *
     * @param  array|Traversable $translations
     * @return self
     * @throws Exception\InvalidArgumentException
     */
    public function setTranslations($translations)
    {
        if (! is_array($translations) && ! $translations instanceof Traversable) {
            throw new Exception\InvalidArgumentException(sprintf(
                '"%s" expects an array or Traversable; received "%s"',
                __METHOD__,
                get_debug_type($translations)
            ));
        }

        foreach ($translations as $message => $flag) {
            $this->options['translations'][$message] = (bool) $flag;
        }

        return $this;
    }

    /**
     * @deprecated since 2.37.0 - All option setters and getters will be removed in 3.0
     *
     * @return array
     */
    public function getTranslations()
    {
        return $this->options['translations'] ?? [];
    }

    /**
     * Defined by Laminas\Filter\FilterInterface
     *
     * Returns a boolean representation of $value
     *
     * @param  null|array|bool|float|int|string $value
     * @return bool|mixed
     */
    public function filter($value)
    {
        $type    = $this->getType();
        $casting = $this->getCasting();

        // LOCALIZED
        if ($type & self::TYPE_LOCALIZED) {
            if (is_string($value)) {
                if (isset($this->options['translations'][$value])) {
                    return (bool) $this->options['translations'][$value];
                }
            }
        }

        // FALSE_STRING ('false')
        if ($type & self::TYPE_FALSE_STRING) {
            if (is_string($value) && strtolower($value) === 'false') {
                return false;
            }

            if (! $casting && is_string($value) && strtolower($value) === 'true') {
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
            if (is_array($value) && $value === []) {
                return false;
            }
        }

        // ZERO_STRING ('0')
        if ($type & self::TYPE_ZERO_STRING) {
            if (is_string($value) && $value === '0') {
                return false;
            }

            if (! $casting && is_string($value) && $value === '1') {
                return true;
            }
        }

        // STRING ('')
        if ($type & self::TYPE_STRING) {
            if (is_string($value) && $value === '') {
                return false;
            }
        }

        // FLOAT (0.0)
        if ($type & self::TYPE_FLOAT) {
            if (is_float($value) && $value === 0.0) {
                return false;
            }

            if (! $casting && is_float($value) && $value === 1.0) {
                return true;
            }
        }

        // INTEGER (0)
        if ($type & self::TYPE_INTEGER) {
            if (is_int($value) && $value === 0) {
                return false;
            }

            if (! $casting && is_int($value) && $value === 1) {
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
}
