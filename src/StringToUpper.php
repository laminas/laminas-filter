<?php

declare(strict_types=1);

namespace Laminas\Filter;

use function is_scalar;
use function mb_strtoupper;

/**
 * @psalm-import-type UnicodeOptions from AbstractUnicode
 * @extends AbstractUnicode<UnicodeOptions>
 */
class StringToUpper extends AbstractUnicode
{
    /**
     * @param string|UnicodeOptions|iterable|null $encodingOrOptions
     */
    public function __construct($encodingOrOptions = null)
    {
        if ($encodingOrOptions !== null) {
            if (! static::isOptions($encodingOrOptions)) {
                $this->setEncoding($encodingOrOptions);
            } else {
                $this->setOptions($encodingOrOptions);
            }
        }
    }

    /**
     * Defined by Laminas\Filter\FilterInterface
     *
     * Returns the string $value, converting characters to uppercase as necessary
     *
     * If the value provided is non-scalar, the value will remain unfiltered
     *
     * @param  mixed $value
     * @return string|mixed
     * @psalm-return ($value is scalar ? string : mixed)
     */
    public function filter($value)
    {
        if (! is_scalar($value)) {
            return $value;
        }

        return mb_strtoupper((string) $value, $this->getEncoding());
    }
}
