<?php

declare(strict_types=1);

namespace LaminasTest\Filter;

use Laminas\Filter\AbstractUnicode;
use Laminas\Filter\Exception\InvalidArgumentException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

use function assert;
use function is_string;
use function mb_internal_encoding;
use function strtolower;

class AbstractUnicodeTest extends TestCase
{
    private AbstractUnicode $filter;

    protected function setUp(): void
    {
        parent::setUp();

        $this->filter = new class extends AbstractUnicode {
            public function filter(mixed $value): mixed
            {
                assert(is_string($value));
                return strtolower($value);
            }
        };
    }

    /** @return list<array{0: string, 1: string}> */
    public static function encodingProvider(): array
    {
        return [
            ['ISO-8859-16', 'iso-8859-16'],
            ['UTF-8', 'utf-8'],
            ['Windows-1251', 'windows-1251'],
        ];
    }

    #[DataProvider('encodingProvider')]
    public function testThatEncodingOptionIsLowerCased(string $encoding, string $expectedEncoding): void
    {
        $this->filter->setEncoding($encoding);
        self::assertNotSame($encoding, $this->filter->getEncoding());
        self::assertSame($expectedEncoding, $this->filter->getEncoding());
    }

    public function testThatAnUnSupportedEncodingCausesAnException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Encoding \'goats\' is not supported by mbstring extension');

        $this->filter->setEncoding('Goats');
    }

    public function testThatMbStringInternalEncodingIsReturnedWhenEncodingHasNotBeenSpecified(): void
    {
        $expect = mb_internal_encoding();
        self::assertSame($expect, $this->filter->getEncoding());
    }

    public function testThatExplicitlySettingEncodingToNullWillYieldDefaultEncoding(): void
    {
        $this->filter->setEncoding(null);
        self::assertSame(mb_internal_encoding(), $this->filter->getEncoding());
    }
}
