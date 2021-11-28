<?php

declare(strict_types=1);

namespace TestNamespace;

use Laminas\Validator\AbstractValidator;

use function current;

/**
 * Mock file for testbed
 */
class StringEquals extends AbstractValidator
{
    public const NOT_EQUALS = 'stringNotEquals';

    /**
     * Array with message templates
     *
     * @var array
     */
    protected $messageTemplates = [
        self::NOT_EQUALS => 'Not all strings in the argument are equal',
    ];

    /**
     * Defined by Laminas_Validate_Interface
     *
     * Returns true if and only if all the elements of the array argument
     * are equal to one another with string comparison.
     *
     * @param  array $value Value to validate
     * @return bool
     */
    public function isValid($value)
    {
        $this->setValue($value);

        $initial = (string) current((array) $value);
        foreach ((array) $value as $element) {
            if ((string) $element !== $initial) {
                $this->error(self::NOT_EQUALS);
                return false;
            }
        }

        return true;
    }
}
