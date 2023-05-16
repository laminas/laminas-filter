<?php

declare(strict_types=1);

namespace LaminasTest\Filter\TestAsset;

final class CallbackClass
{
    public static function staticCallback(string $value): string
    {
        return 'staticCallback-' . $value;
    }

    public function objectCallbackWithParams(string $value, int|string|null $param = null): string
    {
        return 'objectCallbackWithParams-' . $value . '-' . (string) $param;
    }

    public function __invoke(string $value): string
    {
        return 'stringClassCallback-' . $value;
    }

    public function objectCallback(string $value): string
    {
        return 'objectCallback-' . $value;
    }
}
