<?php

/**
 * @see       https://github.com/laminas/laminas-filter for the canonical source repository
 * @copyright https://github.com/laminas/laminas-filter/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-filter/blob/master/LICENSE.md New BSD License
 */

namespace TestNamespace;

use Zend\Validator\AbstractValidator;

/**
 * Mock file for testbed
 */
class StringEquals extends AbstractValidator
{

    const NOT_EQUALS = 'stringNotEquals';

    /**
     * Array with message templates
     *
     * @var array
     */
    protected $messageTemplates = array(
        self::NOT_EQUALS => 'Not all strings in the argument are equal'
    );

    /**
     * Defined by Zend_Validate_Interface
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
