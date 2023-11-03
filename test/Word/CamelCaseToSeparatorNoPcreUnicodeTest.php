<?php

declare(strict_types=1);

namespace LaminasTest\Filter\Word;

use Laminas\Stdlib\StringUtils;
use ReflectionClass;

/**
 * Test class for Laminas\Filter\Word\CamelCaseToSeparator which simulates the
 * PCRE Unicode features disabled
 */
class CamelCaseToSeparatorNoPcreUnicodeTest extends CamelCaseToSeparatorTest
{
    public function setUp(): void
    {
        if (! StringUtils::hasPcreUnicodeSupport()) {
            self::markTestSkipped('PCRE is not compiled with Unicode support');
        }

        $reflectionClass = new ReflectionClass(StringUtils::class);
        $reflectionClass->setStaticPropertyValue('hasPcreUnicodeSupport', false);
    }

    public function tearDown(): void
    {
        $reflectionClass = new ReflectionClass(StringUtils::class);
        $reflectionClass->setStaticPropertyValue('hasPcreUnicodeSupport', true);
    }
}
