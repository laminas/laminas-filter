<?php

declare(strict_types=1);

namespace LaminasTest\Filter\Compress;

use Laminas\Filter\Compress\Tar as TarCompression;
use Laminas\Filter\Exception\ExtensionNotLoadedException;
use PHPUnit\Framework\TestCase;

use function class_exists;
use function restore_error_handler;
use function set_error_handler;

use const E_DEPRECATED;

class TarLoadArchiveTarTest extends TestCase
{
    public function testArchiveTarNotLoaded()
    {
        set_error_handler(function ($errno, $errstr) {
            // PEAR class uses deprecated constructor, which emits a deprecation error
            return true;
        }, E_DEPRECATED);
        if (class_exists('Archive_Tar')) {
            restore_error_handler();
            $this->markTestSkipped('PEAR Archive_Tar is present; skipping test that expects its absence');
        }
        restore_error_handler();

        try {
            $tar = new TarCompression();
            $this->fail('ExtensionNotLoadedException was expected but not thrown');
        } catch (ExtensionNotLoadedException $e) {
        }
    }
}
