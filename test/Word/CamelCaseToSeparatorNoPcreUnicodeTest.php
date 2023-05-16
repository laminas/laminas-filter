<?php

declare(strict_types=1);

namespace LaminasTest\Filter\Word;

use Laminas\Stdlib\StringUtils;
use ReflectionProperty;

/**
 * Test class for Laminas\Filter\Word\CamelCaseToSeparator which simulates the
 * PCRE Unicode features disabled
 */
class CamelCaseToSeparatorNoPcreUnicodeTest extends CamelCaseToSeparatorTest
{
    protected ReflectionProperty $reflection;

    public function setUp(): void
    {
        if (! StringUtils::hasPcreUnicodeSupport()) {
            self::markTestSkipped('PCRE is not compiled with Unicode support');
        }

        $this->reflection = new ReflectionProperty(StringUtils::class, 'hasPcreUnicodeSupport');
        $this->reflection->setValue(false);
    }

    public function tearDown(): void
    {
        $this->reflection->setValue(true);
    }
}
