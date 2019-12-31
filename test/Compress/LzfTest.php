<?php

/**
 * @see       https://github.com/laminas/laminas-filter for the canonical source repository
 * @copyright https://github.com/laminas/laminas-filter/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-filter/blob/master/LICENSE.md New BSD License
 */

namespace LaminasTest\Filter\Compress;

use Laminas\Filter\Compress\Llaminas as LlaminasCompression;

/**
 * @group      Laminas_Filter
 */
class LlaminasTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        if (!extension_loaded('llaminas')) {
            $this->markTestSkipped('This adapter needs the llaminas extension');
        }
    }

    /**
     * Basic usage
     *
     * @return void
     */
    public function testBasicUsage()
    {
        $filter  = new LlaminasCompression();

        $text       = 'compress me';
        $compressed = $filter->compress($text);
        $this->assertNotEquals($text, $compressed);

        $decompressed = $filter->decompress($compressed);
        $this->assertEquals($text, $decompressed);
    }

    /**
     * testing toString
     *
     * @return void
     */
    public function testLlaminasToString()
    {
        $filter = new LlaminasCompression();
        $this->assertEquals('Llaminas', $filter->toString());
    }
}
