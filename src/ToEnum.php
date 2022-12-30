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
 * @psalm-type Options array{enum: class-string<UnitEnum>}
 * @extends AbstractFilter<Options>
 */
class ToEnum extends AbstractFilter
{
    /** @var Options */
    protected $options = [
        'enum' => null,
    ];

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
            $this->setOptions($enumOrOptions);

            return;
        }

        if (is_string($enumOrOptions)) {
            $this->setEnum($enumOrOptions);
        }
    }

    /**
     * @param class-string<UnitEnum> $enum
     */
    public function setEnum(string $enum): self
    {
        if (! is_subclass_of($enum, UnitEnum::class)) {
            throw new Exception\InvalidArgumentException(
                'enum is not of type enum'
            );
        }

        $this->options['enum'] = $enum;
        return $this;
    }

    /**
     * @return class-string<UnitEnum>|null
     */
    public function getEnum(): ?string
    {
        return $this->options['enum'];
    }

    /**
     * Defined by Laminas\Filter\FilterInterface
     *
     * Returns an enum representation of $value or null
     *
     * @param  null|array|bool|float|int|string $value
     * @return UnitEnum|BackedEnum|null
     */
    public function filter($value)
    {
        $enum = $this->getEnum();

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
