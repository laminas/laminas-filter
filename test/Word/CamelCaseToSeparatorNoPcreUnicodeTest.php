<?php

/**
 * @see       https://github.com/laminas/laminas-filter for the canonical source repository
 * @copyright https://github.com/laminas/laminas-filter/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-filter/blob/master/LICENSE.md New BSD License
 */

namespace LaminasTest\Filter\Word;

use Laminas\Stdlib\StringUtils;
use ReflectionProperty;

/**
 * Test class for Laminas\Filter\Word\CamelCaseToSeparator which simulates the
 * PCRE Unicode features disabled
 */
class CamelCaseToSeparatorNoPcreUnicodeTest extends CamelCaseToSeparatorTest
{
    protected $reflection;

    public function setUp(): void
    {
        if (! StringUtils::hasPcreUnicodeSupport()) {
            $this->markTestSkipped('PCRE is not compiled with Unicode support');
            return;
        }

        $this->reflection = new ReflectionProperty('Laminas\Stdlib\StringUtils', 'hasPcreUnicodeSupport');
        $this->reflection->setAccessible(true);
        $this->reflection->setValue(false);
    }

    public function tearDown(): void
    {
        $this->reflection->setValue(true);
    }
}
