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
            self::markTestSkipped('This filter needs the mcrypt or openssl extension');
        }
    }

    /**
     * Ensures that the filter follows expected behavior
     */
    public function testBasicBlockCipher(): void
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
        self::assertSame('testkey', $enc['key']);
        foreach ($valuesExpected as $input => $output) {
            self::assertSame($output, $filter->encrypt($input));
        }
    }

    /**
     * Ensures that the vector can be set / returned
     */
    public function testGetSetVector(): void
    {
        $filter = new BlockCipherEncryption(['key' => 'testkey']);
        $filter->setVector('1234567890123456');
        self::assertSame('1234567890123456', $filter->getVector());
    }

    public function testWrongSizeVector(): void
    {
        $this->expectException(Exception\InvalidArgumentException::class);
        $filter = new BlockCipherEncryption(['key' => 'testkey']);
        $filter->setVector('testvect');
    }

    /**
     * Ensures that the filter allows default encryption
     */
    public function testDefaultEncryption(): void
    {
        $filter = new BlockCipherEncryption(['key' => 'testkey']);
        $filter->setVector('1234567890123456');
        self::assertSame(
            [
                'key'           => 'testkey',
                'key_iteration' => 5000,
                'algorithm'     => 'aes',
                'hash'          => 'sha256',
                'vector'        => '1234567890123456',
            ],
            $filter->getEncryption()
        );
    }

    /**
     * Ensures that the filter allows setting options de/encryption
     */
    public function testGetSetEncryption(): void
    {
        $filter = new BlockCipherEncryption(['key' => 'testkey']);
        $filter->setVector('1234567890123456');
        $filter->setEncryption(
            ['algorithm' => 'aes']
        );
        self::assertSame(
            [
                'algorithm'     => 'aes',
                'key'           => 'testkey',
                'key_iteration' => 5000,
                'hash'          => 'sha256',
                'vector'        => '1234567890123456',
            ],
            $filter->getEncryption()
        );
    }

    /**
     * Ensures that the filter allows de/encryption
     */
    public function testEncryptionWithDecryption(): void
    {
        $filter = new BlockCipherEncryption(['key' => 'testkey']);
        $filter->setVector('1234567890123456');
        $output = $filter->encrypt('teststring');

        self::assertNotEquals('teststring', $output);

        $input = $filter->decrypt($output);
        self::assertSame('teststring', trim($input));
    }

    public function testConstructionWithStringKey(): void
    {
        $filter = new BlockCipherEncryption('testkey');
        $data   = $filter->getEncryption();
        self::assertSame('testkey', $data['key']);
    }

    public function testConstructionWithInteger(): void
    {
        $this->expectException(Exception\InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid options argument');
        $filter = new BlockCipherEncryption(1234);
    }

    public function testToString(): void
    {
        $filter = new BlockCipherEncryption('testkey');
        self::assertSame('BlockCipher', $filter->toString());
    }

    public function testSettingEncryptionOptions(): void
    {
        $filter = new BlockCipherEncryption('testkey');
        $filter->setEncryption('newkey');
        $test = $filter->getEncryption();
        self::assertSame('newkey', $test['key']);

        try {
            $filter->setEncryption(1234);
            $filter->fail();
        } catch (InvalidArgumentException $e) {
            self::assertStringContainsString('Invalid options argument', $e->getMessage());
        }

        try {
            $filter->setEncryption(['algorithm' => 'unknown']);
            $filter->fail();
        } catch (InvalidArgumentException $e) {
            self::assertStringContainsString('The algorithm', $e->getMessage());
        }

        try {
            $filter->setEncryption(['mode' => 'unknown']);
        } catch (InvalidArgumentException $e) {
            self::assertStringContainsString('The mode', $e->getMessage());
        }
    }

    public function testSettingEmptyVector(): void
    {
        $filter = new BlockCipherEncryption('newkey');
        $this->expectException(Exception\InvalidArgumentException::class);
        $this->expectExceptionMessage('The salt (IV) cannot be empty');
        $filter->setVector('');
    }

    /**
     * Ensures that the filter allows de/encryption with compression
     */
    public function testEncryptionWithDecryptionAndCompression(): void
    {
        if (! extension_loaded('bz2')) {
            self::markTestSkipped('This adapter needs the bz2 extension');
        }

        $filter = new BlockCipherEncryption(['key' => 'testkey']);
        $filter->setVector('1234567890123456');
        $filter->setCompression('bz2');
        $output = $filter->encrypt('teststring');

        self::assertNotEquals('teststring', $output);

        $input = $filter->decrypt($output);
        self::assertSame('teststring', trim($input));
    }
}
