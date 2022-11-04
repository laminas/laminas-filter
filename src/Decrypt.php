<?php

declare(strict_types=1);

namespace Laminas\Filter;

use function is_string;

/**
 * Decrypts a given string
 *
 * @deprecated Since 2.24.0. This filter will be removed in 3.0. You are encouraged to use an alternative encryption
 *             library and write your own filter.
 */
class Decrypt extends Encrypt
{
    /**
     * Defined by Laminas\Filter\Filter
     *
     * Decrypts the content $value with the defined settings
     *
     * @param  string $value Content to decrypt
     * @return string The decrypted content
     */
    public function filter($value)
    {
        if (! is_string($value)) {
            return $value;
        }

        return $this->adapter->decrypt($value);
    }
}
