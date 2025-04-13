<?php

declare(strict_types=1);

namespace Laminas\Filter;

use BackedEnum;
use UnitEnum;

use function assert;
use function constant;
use function defined;
use function is_a;
use function is_int;
use function is_string;

/**
 * @psalm-type Options = array{
 *     enum: class-string<UnitEnum>|class-string<BackedEnum>,
 * }
 * @implements FilterInterface<UnitEnum|BackedEnum>
 */
final class ToEnum implements FilterInterface
{
    /** @var class-string<UnitEnum>|class-string<BackedEnum> */
    private readonly string $enum;

    /** @param Options $options */
    public function __construct(array $options)
    {
        $this->enum = $options['enum'];
    }

    public function filter(mixed $value): mixed
    {
        if (! is_int($value) && ! is_string($value)) {
            return $value;
        }

        if (is_a($this->enum, BackedEnum::class, true)) {
            $enum         = null;
            $stringBacked = is_string($this->enum::cases()[0]->value);
            if ($stringBacked && is_string($value)) {
                $enum = $this->enum::tryFrom($value);
            }

            if (! $stringBacked && (is_int($value) || ((string) (int) $value === $value))) {
                $enum = $this->enum::tryFrom((int) $value);
            }

            if ($enum !== null) {
                return $enum;
            }
        }

        if (! is_string($value)) {
            return $value;
        }

        $constantName = $this->enum . '::' . $value;

        if (defined($constantName)) {
            /** @psalm-suppress MixedAssignment */
            $enum = constant($constantName);
            assert($enum instanceof $this->enum);

            return $enum;
        }

        return $value;
    }

    public function __invoke(mixed $value): mixed
    {
        return $this->filter($value);
    }
}
