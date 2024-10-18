<?php

declare(strict_types=1);

namespace Laminas\Filter;

use Laminas\Filter\Exception\InvalidArgumentException;

use function array_search;
use function is_int;
use function is_string;
use function sprintf;

/**
 * @psalm-type TypeArgument =
 *             int-mask-of<self::TYPE_*>|value-of<self::CONSTANTS>|list<value-of<self::CONSTANTS>>|list<self::TYPE_*>
 * @psalm-type Options = array{
 *     type?: TypeArgument,
 * }
 * @implements FilterInterface<null>
 */
final class ToNull implements FilterInterface
{
    public const TYPE_BOOLEAN     = 1;
    public const TYPE_INTEGER     = 2;
    public const TYPE_EMPTY_ARRAY = 4;
    public const TYPE_STRING      = 8;
    public const TYPE_ZERO_STRING = 16;
    public const TYPE_FLOAT       = 32;
    public const TYPE_ALL         = 63;

    private const CONSTANTS = [
        self::TYPE_BOOLEAN     => 'boolean',
        self::TYPE_INTEGER     => 'integer',
        self::TYPE_EMPTY_ARRAY => 'array',
        self::TYPE_STRING      => 'string',
        self::TYPE_ZERO_STRING => 'zero',
        self::TYPE_FLOAT       => 'float',
        self::TYPE_ALL         => 'all',
    ];

    /** @var int-mask-of<self::TYPE_*> */
    private readonly int $type;

    /** @param Options $options */
    public function __construct(array $options = [])
    {
        $this->type = $this->resolveType($options['type'] ?? self::TYPE_ALL);
    }

    /**
     * @param TypeArgument $type
     * @return int-mask-of<self::TYPE_*>
     */
    private function resolveType(int|array|string $type): int
    {
        if (is_int($type) || is_string($type)) {
            $type = [$type];
        }

        $resolved = 0;

        foreach ($type as $value) {
            $resolved |= is_int($value)
                ? $this->assertValidInteger($value)
                : $this->assertValidTypeString($value);
        }

        /** @psalm-var int-mask-of<self::TYPE_*> - Psalm cannot verify the value here */
        return $resolved;
    }

    /** @return self::TYPE_* */
    private function assertValidTypeString(string $value): int
    {
        $key = array_search($value, self::CONSTANTS, true);
        if ($key === false) {
            throw new InvalidArgumentException(sprintf(
                'Invalid type identifier: "%s"',
                $value,
            ));
        }

        return $key;
    }

    /** @return self::TYPE_* */
    private function assertValidInteger(int $value): int
    {
        if (($value & self::TYPE_ALL) !== $value) {
            throw new InvalidArgumentException(sprintf(
                'Invalid type integer: "%d"',
                $value,
            ));
        }

        /** @psalm-var self::TYPE_* Psalm cannot verify this */
        return $value;
    }

    /**
     * Returns null representation of $value, if value is empty and matches types that should be considered null.
     */
    public function filter(mixed $value): mixed
    {
        // FLOAT (0.0)
        if (($this->type & self::TYPE_FLOAT) !== 0 && $value === 0.0) {
            return null;
        }

        // STRING ZERO ('0')
        if (($this->type & self::TYPE_ZERO_STRING) !== 0 && $value === '0') {
            return null;
        }

        // STRING ('')
        if (($this->type & self::TYPE_STRING) !== 0 && $value === '') {
            return null;
        }

        // EMPTY_ARRAY (array())
        if (($this->type & self::TYPE_EMPTY_ARRAY) !== 0 && $value === []) {
            return null;
        }

        // INTEGER (0)
        if (($this->type & self::TYPE_INTEGER) !== 0 && $value === 0) {
            return null;
        }

        // BOOLEAN (false)
        if (($this->type & self::TYPE_BOOLEAN) !== 0 && $value === false) {
            return null;
        }

        return $value;
    }

    public function __invoke(mixed $value): mixed
    {
        return $this->filter($value);
    }
}
