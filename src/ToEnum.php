<?php

declare(strict_types=1);

namespace Laminas\Filter;

use BackedEnum;
use Laminas\Filter\Exception\RuntimeException;
use Laminas\Stdlib\ArrayUtils;
use Traversable;
use UnitEnum;

use function is_array;
use function is_int;
use function is_string;
use function is_subclass_of;

/**
 * @psalm-type Options = array{
 *     enum: class-string<UnitEnum>,
 * }
 */
final class ToEnum implements FilterInterface
{
    /**
     * @var class-string<UnitEnum>|null
     */
    private ?string $enumClass = null;

    /**
     * @param Traversable|class-string<UnitEnum>|Options $enumOrOptions
     */
    public function __construct($enumOrOptions)
    {
        if ($enumOrOptions instanceof Traversable) {
            /** @var Options $enumOrOptions */
            $enumOrOptions = ArrayUtils::iteratorToArray($enumOrOptions);
        }

        if (
            is_array($enumOrOptions) &&
            isset($enumOrOptions['enum'])
        ) {
            $this->enumClass = $enumOrOptions['enum'];
        }

        if (is_string($enumOrOptions)) {
            $this->enumClass = $enumOrOptions;
        }
    }

    /**
     * Defined by Laminas\Filter\FilterInterface
     *
     * Returns an enum representation of $value if matching.
     *
     * @param  mixed $value
     * @return UnitEnum|mixed
     */
    public function filter($value): mixed
    {
        $enum = $this->enumClass;

        if ($enum === null) {
            throw new RuntimeException(
                'enum class not set'
            );
        }

        if (! is_string($value) && ! is_int($value)) {
            return $value;
        }

        if (is_subclass_of($enum, BackedEnum::class)) {
            return $enum::tryFrom($value) ?: $value;
        }

        if (! is_subclass_of($enum, UnitEnum::class)) {
            return $value;
        }

        if (in_array($value, array_column($enum::cases(), 'name'), true)) {
            return constant($enum . '::' . $value);
        }

        return $value;
    }
}
