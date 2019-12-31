<?php

/**
 * @see       https://github.com/laminas/laminas-filter for the canonical source repository
 * @copyright https://github.com/laminas/laminas-filter/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-filter/blob/master/LICENSE.md New BSD License
 */

namespace ZendTest\Filter\Compress;

use Zend\Filter\Compress\Lzf as LzfCompression;

/**
 * @group      Zend_Filter
 */
class LzfTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        if (!extension_loaded('lzf')) {
            $this->markTestSkipped('This adapter needs the lzf extension');
        }
    }

    /**
     * Basic usage
     *
     * @return void
     */
    public function testBasicUsage()
    {
        $filter  = new LzfCompression();

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
    public function testLzfToString()
    {
        $filter = new LzfCompression();
        $this->assertEquals('Lzf', $filter->toString());
    }
}
