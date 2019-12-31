<?php

/**
 * @see       https://github.com/laminas/laminas-filter for the canonical source repository
 * @copyright https://github.com/laminas/laminas-filter/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-filter/blob/master/LICENSE.md New BSD License
 */

namespace TestNamespace;

use Zend\Validator\ValidatorBroker as BaseValidatorBroker;

require_once __DIR__ . '/ValidatorLoader.php';

class ValidatorBroker extends BaseValidatorBroker
{
    protected $defaultClassLoader = 'TestNamespace\ValidatorLoader';
}
