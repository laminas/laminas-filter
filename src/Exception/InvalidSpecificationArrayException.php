<?php

declare(strict_types=1);

namespace Laminas\Filter\Exception;

use InvalidArgumentException;

use function get_debug_type;
use function sprintf;

final class InvalidSpecificationArrayException extends InvalidArgumentException implements ExceptionInterface
{
    public static function becauseTheFilterListMustBeAnArray(mixed $spec): self
    {
        return new self(sprintf(
            'The `filters` key must be a list of arrays or filter instances. Received %s',
            get_debug_type($spec),
        ));
    }

    public static function becauseFilterSpecMustBeAnArray(mixed $spec): self
    {
        return new self(sprintf(
            'Each member of the `filters` list must be array specification. Received %s',
            get_debug_type($spec),
        ));
    }

    public static function becauseFilterNamesMustBeAString(mixed $name): self
    {
        return new self(sprintf(
            'Individual filter array specifications must have the key `name` that references a filter by its '
            . 'fully qualified class name or an alias configured in the plugin manager. Received %s',
            get_debug_type($name),
        ));
    }

    public static function becauseOptionsShouldBeArrays(mixed $options): self
    {
        return new self(sprintf(
            'Filter options must be an array when specified. Received %s',
            get_debug_type($options),
        ));
    }

    public static function becauseFilterPriorityMustBeAnInteger(mixed $priority): self
    {
        return new self(sprintf(
            'Filter priorities must be integers when specified. Received %s',
            get_debug_type($priority),
        ));
    }

    public static function becauseCallbackListMustBeAList(mixed $spec): self
    {
        return new self(sprintf(
            'The `callbacks` key must be a list of arrays. Received %s',
            get_debug_type($spec),
        ));
    }

    public static function becauseCallbackMustBeArray(mixed $spec): self
    {
        return new self(sprintf(
            'All items listed under the `callbacks` key must be arrays. Received %s',
            get_debug_type($spec),
        ));
    }

    public static function becauseCallbackMustBePresent(mixed $callback): self
    {
        return new self(sprintf(
            'Each callback filter listed under `callbacks` must contain a callable under the key '
            . '`callback`. Received %s',
            get_debug_type($callback),
        ));
    }
}
