<?php

/**
 * @see       https://github.com/laminas/laminas-filter for the canonical source repository
 * @copyright https://github.com/laminas/laminas-filter/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-filter/blob/master/LICENSE.md New BSD License
 */

namespace LaminasTest\Filter\Compress;

use Laminas\Filter\Compress\Tar as TarCompression;
use Laminas\Filter\Exception\ExtensionNotLoadedException;

/**
 * @group      Laminas_Filter
 */
class TarLoadArchveTarTest extends \PHPUnit_Framework_TestCase
{
    public function testArchiveTarNotLoaded()
    {
        if (class_exists('Archive_Tar')) {
            $this->markTestSkipped('PEAR Archive_Tar is present; skipping test that expects its absence');
        }
        try {
            $tar = new TarCompression;
            $this->fail('ExtensionNotLoadedException was expected but not thrown');
        } catch (ExtensionNotLoadedException $e) {
        }
    }
}
