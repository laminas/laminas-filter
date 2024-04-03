<?php

declare(strict_types=1);

namespace LaminasTest\Filter;

use Laminas\Filter\UriNormalize;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use stdClass;

/**
 * @deprecated
 *
 * @todo Remove this test in v3.0
 */
class UriNormalizeTest extends TestCase
{
    #[DataProvider('abnormalUriProvider')]
    public function testUrisAreNormalized(string $url, string $expected): void
    {
        $filter = new UriNormalize();
        $result = $filter->filter($url);
        self::assertSame($expected, $result);
    }

    public function testDefaultSchemeAffectsNormalization(): void
    {
        $this->markTestIncomplete();
    }

    #[DataProvider('enforcedSchemeTestcaseProvider')]
    public function testEnforcedScheme(string $scheme, string $input, string $expected): void
    {
        $filter = new UriNormalize(['enforcedScheme' => $scheme]);
        $result = $filter->filter($input);
        self::assertSame($expected, $result);
    }

    /** @return list<array{0: string, 1: string}> */
    public static function abnormalUriProvider(): array
    {
        return [
            ['http://www.example.com', 'http://www.example.com/'],
            ['hTTp://www.example.com/ space', 'http://www.example.com/%20space'],
            ['file:///www.example.com/foo/bar', 'file:///www.example.com/foo/bar'], // this should not be affected
            ['file:///home/shahar/secret/../../otherguy/secret', 'file:///home/otherguy/secret'],
            ['https://www.example.com:443/hasport', 'https://www.example.com/hasport'],
            ['/foo/bar?q=%711', '/foo/bar?q=q1'], // no scheme enforced
        ];
    }

    /** @return list<string[]> */
    public static function enforcedSchemeTestcaseProvider(): array
    {
        return [
            ['ftp', 'http://www.example.com', 'http://www.example.com/'], // no effect - this one has a scheme
            ['mailto', 'mailto:shahar@example.com', 'mailto:shahar@example.com'],
            ['http', 'www.example.com/foo/bar?q=q', 'http://www.example.com/foo/bar?q=q'],
            ['ftp', 'www.example.com/path/to/file.ext', 'ftp://www.example.com/path/to/file.ext'],
            ['http', '/just/a/path', '/just/a/path'], // cannot be enforced, no host
            ['http', '', ''],
        ];
    }

    /** @return list<array> */
    public static function returnUnfilteredDataProvider(): array
    {
        return [
            [null],
            [new stdClass()],
            [
                [
                    'http://www.example.com',
                    'file:///home/shahar/secret/../../otherguy/secret',
                ],
            ],
        ];
    }

    #[DataProvider('returnUnfilteredDataProvider')]
    public function testReturnUnfiltered(mixed $input): void
    {
        $filter = new UriNormalize();

        self::assertSame($input, $filter($input));
    }
}
