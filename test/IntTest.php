<?php

declare(strict_types=1);

namespace LaminasTest\Filter;

use Laminas\Filter\Int as IntFilter;
use PHPUnit\Framework\TestCase;

use function version_compare;

use const PHP_VERSION;

class IntTest extends TestCase
{
    public function setUp(): void
    {
        if (version_compare(PHP_VERSION, '7.0', '>=')) {
            $this->markTestSkipped('Cannot test Int filter under PHP 7; reserved keyword');
        }
    }

    public function testRaisesNoticeOnInstantiation()
    {
        $this->expectException('PHPUnit_Framework_Error_Deprecated');
        new IntFilter();
    }
}
