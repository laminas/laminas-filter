<?php

declare(strict_types=1);

namespace LaminasTest\Filter\Encrypt;

use Laminas\Filter\Encrypt\BlockCipher as BlockCipherEncryption;
use Laminas\Filter\Exception;
use Laminas\Filter\Exception\InvalidArgumentException;
use PHPUnit\Framework\TestCase;

use function extension_loaded;
use function trim;

class BlockCipherTest extends TestCase
{
    public function setUp(): void
    {
        if (! extension_loaded('mcrypt') && ! extension_loaded('openssl')) {
            $this->markTestSkipped('This filter needs the mcrypt or openssl extension');
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
        // @codingStandardsIgnoreStart
        $valuesExpected = [
            'STRING' => '972c29fe2ac804e7adab21aa15b2896215e2daf227d82f92734da074c24095abTGFtaW5hc19fUHJvamVjdGK1rPNgf9xxr8Croef2PRs=',
            'ABC1@3' => '8b3fcdd53a5833257f27e1a35fa715c0e023da85240f22e32f9fd5ed790431a8TGFtaW5hc19fUHJvamVjdGHsk81/w1rQQXN6RpRYDqI=',
            'A b C' => 'f89c41712b95d1ec48efdf53f23488bae0b2d8cb28915fbf90a8f630bade3dd1TGFtaW5hc19fUHJvamVjdE53RnguL2HyRLU4a5m9RWU='
        ];
        // @codingStandardsIgnoreEnd
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
        $this->expectException(Exception\InvalidArgumentException::class);
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
            [
                'key'           => 'testkey',
                'algorithm'     => 'aes',
                'vector'        => '1234567890123456',
                'key_iteration' => 5000,
                'hash'          => 'sha256',
            ],
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
            ['algorithm' => 'blowfish']
        );
        $this->assertEquals(
            [
                'key'           => 'testkey',
                'algorithm'     => 'blowfish',
                'vector'        => '1234567890123456',
                'key_iteration' => 5000,
                'hash'          => 'sha256',
            ],
            $filter->getEncryption()
        );
    }

    /**
     * Ensures that the filter allows de/encryption
     *
     * @return void
     */
    public function testEncryptionWithDecryption()
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
        $data   = $filter->getEncryption();
        $this->assertEquals('testkey', $data['key']);
    }

    /**
     * @return void
     */
    public function testConstructionWithInteger()
    {
        $this->expectException(Exception\InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid options argument');
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
        } catch (InvalidArgumentException $e) {
            $this->assertStringContainsString('Invalid options argument', $e->getMessage());
        }

        try {
            $filter->setEncryption(['algorithm' => 'unknown']);
            $filter->fail();
        } catch (InvalidArgumentException $e) {
            $this->assertStringContainsString('The algorithm', $e->getMessage());
        }

        try {
            $filter->setEncryption(['mode' => 'unknown']);
        } catch (InvalidArgumentException $e) {
            $this->assertStringContainsString('The mode', $e->getMessage());
        }
    }

    /**
     * @return void
     */
    public function testSettingEmptyVector()
    {
        $filter = new BlockCipherEncryption('newkey');
        $this->expectException(Exception\InvalidArgumentException::class);
        $this->expectExceptionMessage('The salt (IV) cannot be empty');
        $filter->setVector('');
    }

    /**
     * Ensures that the filter allows de/encryption with compression
     *
     * @return void
     */
    public function testEncryptionWithDecryptionAndCompression()
    {
        if (! extension_loaded('bz2')) {
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
