<?php

declare(strict_types=1);

namespace Laminas\Filter\Word;

use Laminas\Filter\FilterInterface;
use Laminas\Filter\ScalarOrArrayFilterCallback;

use function is_array;
use function is_scalar;
use function mb_strlen;
use function mb_strtolower;
use function mb_substr;

/** @implements FilterInterface<string|array<array-key, string|mixed>> */
final class UnderscoreToStudlyCase implements FilterInterface
{
    public function filter(mixed $value): mixed
    {
        if (! is_scalar($value) && ! is_array($value)) {
            return $value;
        }

        /** @var string|array $value */
        $value = (new SeparatorToCamelCase(['separator' => '_']))->filter($value);

        return ScalarOrArrayFilterCallback::applyRecursively(
            $value,
            function (string $input): string {
                if (0 === mb_strlen($input)) {
                    return $input;
                }

                return mb_strtolower(mb_substr($input, 0, 1)) . mb_substr($input, 1);
            }
        );
    }

    public function __invoke(mixed $value): mixed
    {
        return $this->filter($value);
    }
}
