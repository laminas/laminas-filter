<?php

/**
 * @see       https://github.com/laminas/laminas-filter for the canonical source repository
 * @copyright https://github.com/laminas/laminas-filter/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-filter/blob/master/LICENSE.md New BSD License
 */

namespace LaminasTest\Filter\Encrypt;

use Laminas\Filter\Encrypt\BlockCipher as BlockCipherEncryption;
use Laminas\Filter\Exception;

/**
 * @group      Laminas_Filter
 */
class BlockCipherTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        try {
            $filter = new BlockCipherEncryption(['key' => 'testkey']);
        } catch (Exception\RuntimeException $e) {
            $this->markTestSkipped('This adapter needs the mcrypt extension');
        }
    }

    /**
     * Ensures that the filter follows expected behavior
     *
     * @return void
     */
    public function testBasicBlockCipher()
    {
        $filter = new BlockCipherEncryption(['key' => 'testkey']);
        $valuesExpected = [
            'STRING' => '972c29fe2ac804e7adab21aa15b2896215e2daf227d82f92734da074c24095abTGFtaW5hc19fUHJvamVjdGK1rPNgf9xxr8Croef2PRs=',
            'ABC1@3' => '8b3fcdd53a5833257f27e1a35fa715c0e023da85240f22e32f9fd5ed790431a8TGFtaW5hc19fUHJvamVjdGHsk81/w1rQQXN6RpRYDqI=',
            'A b C' => 'f89c41712b95d1ec48efdf53f23488bae0b2d8cb28915fbf90a8f630bade3dd1TGFtaW5hc19fUHJvamVjdE53RnguL2HyRLU4a5m9RWU='
        ];
        $filter->setVector('Laminas__Project');
        $enc = $filter->getEncryption();
        $this->assertEquals('testkey', $enc['key']);
        foreach ($valuesExpected as $input => $output) {
            $this->assertEquals($output, $filter->encrypt($input));
        }
    }

    /**
     * Ensures that the vector can be set / returned
     *
     * @return void
     */
    public function testGetSetVector()
    {
        $filter = new BlockCipherEncryption(['key' => 'testkey']);
        $filter->setVector('1234567890123456');
        $this->assertEquals('1234567890123456', $filter->getVector());
    }

    public function testWrongSizeVector()
    {
        $this->setExpectedException('\Laminas\Filter\Exception\InvalidArgumentException');
        $filter = new BlockCipherEncryption(['key' => 'testkey']);
        $filter->setVector('testvect');
    }
    /**
     * Ensures that the filter allows default encryption
     *
     * @return void
     */
    public function testDefaultEncryption()
    {
        $filter = new BlockCipherEncryption(['key' => 'testkey']);
        $filter->setVector('1234567890123456');
        $this->assertEquals(
            ['key'           => 'testkey',
                  'algorithm'     => 'aes',
                  'vector'        => '1234567890123456',
                  'key_iteration' => 5000,
                  'hash'          => 'sha256'],
            $filter->getEncryption()
        );
    }

    /**
     * Ensures that the filter allows setting options de/encryption
     *
     * @return void
     */
    public function testGetSetEncryption()
    {
        $filter = new BlockCipherEncryption(['key' => 'testkey']);
        $filter->setVector('1234567890123456');
        $filter->setEncryption(
            ['algorithm' => '3des']
        );
        $this->assertEquals(
            ['key'           => 'testkey',
                  'algorithm'     => '3des',
                  'vector'        => '1234567890123456',
                  'key_iteration' => 5000,
                  'hash'          => 'sha256'],
            $filter->getEncryption()
        );
    }

    /**
     * Ensures that the filter allows de/encryption
     *
     * @return void
     */
    public function testEncryptionWithDecryptionMcrypt()
    {
        $filter = new BlockCipherEncryption(['key' => 'testkey']);
        $filter->setVector('1234567890123456');
        $output = $filter->encrypt('teststring');

        $this->assertNotEquals('teststring', $output);

        $input = $filter->decrypt($output);
        $this->assertEquals('teststring', trim($input));
    }

    /**
     * @return void
     */
    public function testConstructionWithStringKey()
    {
        $filter = new BlockCipherEncryption('testkey');
        $data = $filter->getEncryption();
        $this->assertEquals('testkey', $data['key']);
    }

    /**
     * @return void
     */
    public function testConstructionWithInteger()
    {
        $this->setExpectedException('\Laminas\Filter\Exception\InvalidArgumentException', 'Invalid options argument');
        $filter = new BlockCipherEncryption(1234);
    }

    /**
     * @return void
     */
    public function testToString()
    {
        $filter = new BlockCipherEncryption('testkey');
        $this->assertEquals('BlockCipher', $filter->toString());
    }

    /**
     * @return void
     */
    public function testSettingEncryptionOptions()
    {
        $filter = new BlockCipherEncryption('testkey');
        $filter->setEncryption('newkey');
        $test = $filter->getEncryption();
        $this->assertEquals('newkey', $test['key']);

        try {
            $filter->setEncryption(1234);
            $filter->fail();
        } catch (\Laminas\Filter\Exception\InvalidArgumentException $e) {
            $this->assertContains('Invalid options argument', $e->getMessage());
        }

        try {
            $filter->setEncryption(['algorithm' => 'unknown']);
            $filter->fail();
        } catch (\Laminas\Filter\Exception\InvalidArgumentException $e) {
            $this->assertContains('The algorithm', $e->getMessage());
        }

        try {
            $filter->setEncryption(['mode' => 'unknown']);
        } catch (\Laminas\Filter\Exception\InvalidArgumentException $e) {
            $this->assertContains('The mode', $e->getMessage());
        }
    }

    /**
     * @return void
     */
    public function testSettingEmptyVector()
    {
        $filter = new BlockCipherEncryption('newkey');
        $this->setExpectedException('\Laminas\Filter\Exception\InvalidArgumentException', 'The salt (IV) cannot be empty');
        $filter->setVector('');
    }

    /**
     * Ensures that the filter allows de/encryption with compression
     *
     * @return void
     */
    public function testEncryptionWithDecryptionAndCompressionMcrypt()
    {
        if (!extension_loaded('bz2')) {
            $this->markTestSkipped('This adapter needs the bz2 extension');
        }

        $filter = new BlockCipherEncryption(['key' => 'testkey']);
        $filter->setVector('1234567890123456');
        $filter->setCompression('bz2');
        $output = $filter->encrypt('teststring');

        $this->assertNotEquals('teststring', $output);

        $input = $filter->decrypt($output);
        $this->assertEquals('teststring', trim($input));
    }
}
