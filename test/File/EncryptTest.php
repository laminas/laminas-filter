<?php

/**
 * @see       https://github.com/laminas/laminas-filter for the canonical source repository
 * @copyright https://github.com/laminas/laminas-filter/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-filter/blob/master/LICENSE.md New BSD License
 */

namespace LaminasTest\Filter\File;

use Laminas\Filter\File\Decrypt as FileDecrypt;
use Laminas\Filter\File\Encrypt as FileEncrypt;

/**
 * @category   Laminas
 * @package    Laminas_Filter
 * @subpackage UnitTests
 * @group      Laminas_Filter
 */
class EncryptTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        if (!extension_loaded('mcrypt')) {
            $this->markTestSkipped('This filter needs the mcrypt extension');
        }

        if (file_exists(dirname(__DIR__).'/_files/newencryption.txt')) {
            unlink(dirname(__DIR__).'/_files/newencryption.txt');
        }
    }

    public function tearDown()
    {
        if (file_exists(dirname(__DIR__).'/_files/newencryption.txt')) {
            unlink(dirname(__DIR__).'/_files/newencryption.txt');
        }
    }

    /**
     * Ensures that the filter follows expected behavior
     *
     * @return void
     */
    public function testBasic()
    {
        $filter = new FileEncrypt();
        $filter->setFilename(dirname(__DIR__).'/_files/newencryption.txt');

        $this->assertEquals(
            dirname(__DIR__).'/_files/newencryption.txt',
            $filter->getFilename());

        $filter->setKey('1234567890123456');
        $this->assertEquals(dirname(__DIR__).'/_files/newencryption.txt',
            $filter->filter(dirname(__DIR__).'/_files/encryption.txt'));

        $this->assertEquals(
            'Encryption',
            file_get_contents(dirname(__DIR__).'/_files/encryption.txt'));

        $this->assertNotEquals(
            'Encryption',
            file_get_contents(dirname(__DIR__).'/_files/newencryption.txt'));
    }

    public function testEncryptionWithDecryption()
    {
        $filter = new FileEncrypt();
        $filter->setFilename(dirname(__DIR__).'/_files/newencryption.txt');
        $filter->setKey('1234567890123456');
        $this->assertEquals(dirname(__DIR__).'/_files/newencryption.txt',
            $filter->filter(dirname(__DIR__).'/_files/encryption.txt'));

        $this->assertNotEquals(
            'Encryption',
            file_get_contents(dirname(__DIR__).'/_files/newencryption.txt'));

        $filter = new FileDecrypt();
        $filter->setKey('1234567890123456');
        $input = $filter->filter(dirname(__DIR__).'/_files/newencryption.txt');
        $this->assertEquals(dirname(__DIR__).'/_files/newencryption.txt', $input);

        $this->assertEquals(
            'Encryption',
            trim(file_get_contents(dirname(__DIR__).'/_files/newencryption.txt')));
    }

    /**
     * @return void
     */
    public function testNonExistingFile()
    {
        $filter = new FileEncrypt();
        $filter->setKey('1234567890123456');

        $this->setExpectedException('\Laminas\Filter\Exception\InvalidArgumentException', 'not found');
        echo $filter->filter(dirname(__DIR__).'/_files/nofile.txt');
    }

    /**
     * @return void
     */
    public function testEncryptionInSameFile()
    {
        $filter = new FileEncrypt();
        $filter->setKey('1234567890123456');

        copy(dirname(__DIR__).'/_files/encryption.txt', dirname(__DIR__).'/_files/newencryption.txt');
        $filter->filter(dirname(__DIR__).'/_files/newencryption.txt');

        $this->assertNotEquals(
            'Encryption',
            trim(file_get_contents(dirname(__DIR__).'/_files/newencryption.txt')));
    }
}
