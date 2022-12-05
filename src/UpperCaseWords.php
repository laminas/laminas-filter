<?php

declare(strict_types=1);

namespace Laminas\Filter;

use function is_string;
use function mb_convert_case;

use const MB_CASE_TITLE;

/**
 * @psalm-import-type UnicodeOptions from AbstractUnicode
 * @extends AbstractUnicode<UnicodeOptions>
 */
final class UpperCaseWords extends AbstractUnicode
{
    /**
     * {@inheritDoc}
     */
    protected $options = [
        'encoding' => null,
    ];

    /**
     * @param string|UnicodeOptions|iterable|null $encodingOrOptions OPTIONAL
     */
    public function __construct($encodingOrOptions = null)
    {
        if ($encodingOrOptions !== null) {
            if (self::isOptions($encodingOrOptions)) {
                $this->setOptions($encodingOrOptions);
            } else {
                $this->setEncoding($encodingOrOptions);
            }
        }
    }

    /**
     * {@inheritDoc}
     *
     * Returns the string $value, converting words to have an uppercase first character as necessary
     *
     * If the value provided is not a string, the value will remain unfiltered
     *
     * @param  string|mixed $value
     * @return string|mixed
     * @psalm-return ($value is string ? string : mixed)
     */
    public function filter($value)
    {
        if (! is_string($value)) {
            return $value;
        }

        return mb_convert_case((string) $value, MB_CASE_TITLE, $this->getEncoding());
    }
}
