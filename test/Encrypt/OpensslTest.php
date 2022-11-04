<?php

declare(strict_types=1);

namespace LaminasTest\Filter\Encrypt;

use Laminas\Filter\Encrypt\Openssl as OpensslEncryption;
use Laminas\Filter\Exception;
use Laminas\Filter\Exception\RuntimeException;
use OpenSSLAsymmetricKey;
use PHPUnit\Framework\TestCase;
use ReflectionProperty;

use function file_get_contents;
use function openssl_get_cipher_methods;
use function openssl_open;
use function openssl_pkey_get_private;
use function openssl_pkey_get_public;
use function openssl_seal;
use function reset;
use function sprintf;

class OpensslTest extends TestCase
{
    public function setUp(): void
    {
        self::markTestSkipped('The OpenSSL adapter is broken on OpenSSL 3.x and none of these tests pass');
    }

    public function testOpenSslHasRc4Algo(): void
    {
        $list = openssl_get_cipher_methods();
        self::assertContains('rc4', $list);
    }

    /** @return list<array{0: string, 1: string, 2:null|string}> */
    public function keyProvider(): array
    {
        return [
            [__DIR__ . '/openssl/public_4096.pem', __DIR__ . '/openssl/private_unencrypted_4096.pem', null],
            [__DIR__ . '/openssl/public_2048.pem', __DIR__ . '/openssl/private_unencrypted_2048.pem', null],
            [__DIR__ . '/openssl/public_4096.pem', __DIR__ . '/openssl/private_encrypted_4096.pem', 'password'],
            [__DIR__ . '/openssl/public_2048.pem', __DIR__ . '/openssl/private_encrypted_2048.pem', 'password'],
        ];
    }

    /** @dataProvider keyProvider */
    public function testOpenSslSealAndOpen(string $publicPath, string $privatePath, string|null $passphrase): void
    {
        $publicKey = openssl_pkey_get_public(sprintf('file://%s', $publicPath));
        self::assertInstanceOf(OpenSSLAsymmetricKey::class, $publicKey);
        $privateKey = openssl_pkey_get_private(sprintf('file://%s', $privatePath), $passphrase);
        $content    = 'Goats are friends 🐐';
        $result     = openssl_seal($content, $sealedData, $envelopeKeys, [$publicKey], 'RC4');
        self::assertIsInt($result);
        $envelopeKey = reset($envelopeKeys);
        self::assertIsString($envelopeKey);
        $result = openssl_open($sealedData, $decrypted, $envelopeKey, $privateKey, 'RC4');
        self::assertTrue($result);
        self::assertSame($content, $decrypted);
    }

    private static function assertPublicKeyMatchesFile(OpensslEncryption $filter, string $filePath): void
    {
        $public = $filter->getPublicKey();
        self::assertArrayHasKey($filePath, $public);
        self::assertSame(
            file_get_contents($filePath),
            $public[$filePath]
        );
    }

    private static function assertPrivateKeyMatchesFile(OpensslEncryption $filter, string $filePath): void
    {
        $private = $filter->getPrivateKey();
        self::assertArrayHasKey($filePath, $private);
        self::assertSame(
            file_get_contents($filePath),
            $private[$filePath]
        );
    }

    public function testAStringArgumentToTheConstructorIsConsideredAPublicKey(): void
    {
        $path   = __DIR__ . '/openssl/public_2048.pem';
        $filter = new OpensslEncryption($path);
        self::assertPublicKeyMatchesFile($filter, $path);
    }

    public function testSetPublicKeyAcceptsAFilePathToAPublicKeyFile(): void
    {
        $filter = new OpensslEncryption();
        $filter->setPublicKey(__DIR__ . '/openssl/public_4096.pem');
        self::assertNotSame('Hey!', $filter->encrypt('Hey!'));
    }

    public function testSetPublicKeyAcceptsAPemEncodedString(): void
    {
        $filter  = new OpensslEncryption();
        $keyPath = __DIR__ . '/openssl/public_2048.pem';
        $filter->setPublicKey(file_get_contents($keyPath));
        self::assertNotSame('Hey!', $filter->encrypt('Hey!'));
        $public = $filter->getPublicKey();
        self::assertArrayHasKey(0, $public);
        self::assertSame(
            file_get_contents($keyPath),
            $public[0]
        );
    }

    public function testSetPublicKeyThrowsAnExceptionForAnInvalidString(): void
    {
        $filter = new OpensslEncryption();
        $this->expectException(Exception\InvalidArgumentException::class);
        $this->expectExceptionMessage('not valid');
        $filter->setPublicKey('123');
    }

    public function testThePublicKeyCanBeRetrievedFromAFilePath(): void
    {
        $filter  = new OpensslEncryption();
        $keyPath = __DIR__ . '/openssl/public_2048.pem';
        $filter->setPublicKey($keyPath);

        self::assertPublicKeyMatchesFile($filter, $keyPath);
    }

    public function testThatGivenAnArrayOfPublicKeysTheLastOneWins(): void
    {
        $filter   = new OpensslEncryption();
        $path2048 = __DIR__ . '/openssl/public_2048.pem';
        $path4096 = __DIR__ . '/openssl/public_4096.pem';
        $filter->setPublicKey([$path2048, $path4096]);
        $public = $filter->getPublicKey();
        self::assertCount(1, $public);
        self::assertPublicKeyMatchesFile($filter, $path4096);
    }

    public function testThatEncryptionAndDecryptionArePossibleWithPackagedEnvelope(): void
    {
        $filter = new OpensslEncryption([
            'private'    => __DIR__ . '/openssl/private_encrypted_2048.pem',
            'public'     => __DIR__ . '/openssl/public_2048.pem',
            'passphrase' => 'password',
            'package'    => true,
        ]);

        $content = 'Goats are friends';

        $encrypted = $filter->encrypt($content);
        self::assertNotSame($content, $encrypted);
        $decrypted = $filter->decrypt($encrypted);
        self::assertSame($content, $decrypted);
    }

    public function testThatEncryptionAndDecryptionArePossibleWithoutPackagedEnvelope(): void
    {
        $filter = new OpensslEncryption([
            'public'  => __DIR__ . '/openssl/public_2048.pem',
            'package' => false,
        ]);

        $content = 'Goats are friends';

        $encrypted = $filter->encrypt($content);
        self::assertNotSame($content, $encrypted);

        $envelope = $filter->getEnvelopeKey();
        self::assertCount(1, $envelope);
        $envelopeKey = reset($envelope);
        self::assertIsString($envelopeKey);

        $decrypt = new OpensslEncryption([
            'private'    => __DIR__ . '/openssl/private_encrypted_2048.pem',
            'passphrase' => 'password',
            'envelope'   => $envelopeKey,
        ]);

        $decrypted = $decrypt->decrypt($encrypted);
        self::assertSame($content, $decrypted);
    }

    public function testPrivateKeyCanBeAFilePath(): void
    {
        $filter = new OpensslEncryption();
        self::assertCount(0, $filter->getPrivateKey());
        $keyPath = __DIR__ . '/openssl/private_unencrypted_4096.pem';
        $filter->setPrivateKey($keyPath);
        self::assertPrivateKeyMatchesFile($filter, $keyPath);
    }

    public function testPrivateKeyCanBePemEncoded(): void
    {
        $filter  = new OpensslEncryption();
        $keyPath = __DIR__ . '/openssl/private_unencrypted_4096.pem';
        $key     = file_get_contents($keyPath);
        $filter->setPrivateKey($key);
        $private = $filter->getPrivateKey();
        self::assertArrayHasKey(0, $private);
        self::assertSame($key, $private[0]);
    }

    public function testSetPrivateKeyThrowsExceptionForInvalidString(): void
    {
        $filter = new OpensslEncryption();
        $this->expectException(Exception\InvalidArgumentException::class);
        $this->expectExceptionMessage('not valid');
        $filter->setPrivateKey('123');
    }

    public function testToString(): void
    {
        $filter = new OpensslEncryption();
        self::assertSame('Openssl', $filter->toString());
    }

    public function testAnExceptionIsThrownAttemptingToDecryptWithoutAPrivateKey(): void
    {
        $filter = new OpensslEncryption();
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Please give a private key');
        $filter->decrypt('unknown');
    }

    public function testAnExceptionIsThrownAttemptingToDecryptWithoutAnEnvelopeKey(): void
    {
        $filter = new OpensslEncryption();
        $filter->setPrivateKey(__DIR__ . '/openssl/private_unencrypted_2048.pem');
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Please give an envelope key');
        $filter->decrypt('unknown');
    }

    public function testDecryptionIsNotPossibleWithoutAnEnvelopeKeyAndPackageSetAsFalse(): void
    {
        $filter = new OpensslEncryption();
        $filter->setPrivateKey(__DIR__ . '/openssl/private_unencrypted_2048.pem');
        $filter->setEnvelopeKey('unknown');
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('was not able to decrypt');
        $filter->decrypt('unknown');
    }

    public function testEncryptionCannotProceedWithoutPublicKey(): void
    {
        $filter = new OpensslEncryption();

        $this->expectException(Exception\RuntimeException::class);
        $this->expectExceptionMessage('without public key');
        $filter->encrypt('unknown');
    }

    public function testCompressionConfigWillBeUnsetCorrectlyInInternalKeysArray(): void
    {
        $filter = new OpensslEncryption([
            'compression' => 'bz2',
        ]);

        $r = new ReflectionProperty($filter, 'keys');
        $r->setAccessible(true);
        $keys = $r->getValue($filter);
        self::assertIsArray($keys);
        self::assertArrayNotHasKey('compression', $keys);
    }

    public function testEncryptionAndDecryptionWithCompressionAndPackagedKeysIsPossible(): void
    {
        $filter = new OpensslEncryption([
            'public'      => __DIR__ . '/openssl/public_4096.pem',
            'private'     => __DIR__ . '/openssl/private_unencrypted_4096.pem',
            'compression' => 'bz2',
            'package'     => true,
        ]);

        $content = 'Muppets';

        $encrypted = $filter->encrypt($content);
        self::assertNotSame($content, $encrypted);
        self::assertSame($content, $filter->decrypt($encrypted));
    }

    public function testEncryptionAndDecryptionWithCompressionAndWithoutPackagedKeysIsPossible(): void
    {
        $encrypt = new OpensslEncryption([
            'public'      => __DIR__ . '/openssl/public_4096.pem',
            'compression' => 'bz2',
            'package'     => false,
        ]);

        $content = 'Muppets';

        $encrypted = $encrypt->encrypt($content);
        self::assertNotSame($content, $encrypted);
        $envelope = $encrypt->getEnvelopeKey();
        self::assertCount(1, $envelope);
        $envelopeKey = reset($envelope);
        self::assertIsString($envelopeKey);

        $decrypt = new OpensslEncryption([
            'private'     => __DIR__ . '/openssl/private_unencrypted_4096.pem',
            'envelope'    => $envelopeKey,
            'compression' => 'bz2',
        ]);

        self::assertSame($content, $decrypt->decrypt($encrypted));
    }

    public function testTheSameCompressionOptionsMustBeUsedForBothEncryptionAndDecryption(): void
    {
        $encrypt = new OpensslEncryption([
            'public'      => __DIR__ . '/openssl/public_4096.pem',
            'compression' => 'gz',
            'package'     => true,
        ]);

        $content = 'Muppets';

        $encrypted = $encrypt->encrypt($content);
        self::assertNotSame($content, $encrypted);

        $decrypt = new OpensslEncryption([
            'private'     => __DIR__ . '/openssl/private_unencrypted_4096.pem',
            'compression' => 'bz2',
            'package'     => true,
        ]);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Error during decompression');

        $decrypt->decrypt($encrypted);
    }

    public function testPassphraseIsMutableAtRuntime(): void
    {
        $filter = new OpensslEncryption();
        self::assertNull($filter->getPassphrase());
        $filter->setPassphrase('secret');
        self::assertSame('secret', $filter->getPassphrase());
    }
}
