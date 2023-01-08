<?php

declare(strict_types=1);

namespace Laminas\Filter;

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
     * @param class-string<UnitEnum>|Traversable|Options $enumOrOptions
     */
    public function __construct($enumOrOptions)
    {
        if ($enumOrOptions instanceof Traversable) {
            $enumOrOptions = ArrayUtils::iteratorToArray($enumOrOptions);
        }

        if (
            is_array($enumOrOptions) &&
            isset($enumOrOptions['enum'])
        ) {
            $this->setEnum($enumOrOptions['enum']);

            return;
        }

        if (is_string($enumOrOptions)) {
            $this->setEnum($enumOrOptions);
        }
    }

    /**
     * @param class-string<UnitEnum> $enum
     */
    protected function setEnum(string $enum): self
    {
        $this->enumClass = $enum;

        return $this;
    }

    /**
     * Defined by Laminas\Filter\FilterInterface
     *
     * Returns an enum representation of $value or null
     *
     * @param  mixed $value
     * @return UnitEnum|null
     */
    public function filter($value): ?UnitEnum
    {
        $enum = $this->enumClass;

        if ($enum === null) {
            throw new RuntimeException(
                'enum class not set'
            );
        }

        if (! is_string($value) && ! is_int($value)) {
            return null;
        }

        if (is_subclass_of($enum, 'BackedEnum')) {
            return $enum::tryFrom($value);
        }

        if (! is_string($value) || ! is_subclass_of($enum, 'UnitEnum')) {
            return null;
        }

        foreach ($enum::cases() as $enumCase) {
            if ($enumCase->name === $value) {
                return $enumCase;
            }
        }

        return null;
    }
}
