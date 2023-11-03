<?php

declare(strict_types=1);

namespace LaminasTest\Filter\Word;

use Laminas\Stdlib\StringUtils;
use ReflectionClass;
use ReflectionProperty;

use const PHP_VERSION_ID;

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

        if (PHP_VERSION_ID >= 80300) {
            $reflectionClass = new ReflectionClass(StringUtils::class);
            $reflectionClass->setStaticPropertyValue('hasPcreUnicodeSupport', false);
            return;
        }

        $reflection = new ReflectionProperty(StringUtils::class, 'hasPcreUnicodeSupport');
        $reflection->setValue(false);
    }

    public function tearDown(): void
    {
        if (PHP_VERSION_ID >= 80300) {
            $reflectionClass = new ReflectionClass(StringUtils::class);
            $reflectionClass->setStaticPropertyValue('hasPcreUnicodeSupport', true);
            return;
        }

        $reflection = new ReflectionProperty(StringUtils::class, 'hasPcreUnicodeSupport');
        $reflection->setValue(true);
    }
}
