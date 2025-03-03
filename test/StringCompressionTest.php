<?php

declare(strict_types=1);

namespace LaminasTest\Filter;

use Laminas\Filter\Compress\StringCompressionAdapterInterface;
use Laminas\Filter\CompressString;
use Laminas\Filter\DecompressString;
use Laminas\Filter\Exception\RuntimeException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

use function range;
use function sprintf;

#[CoversClass(CompressString::class)]
#[CoversClass(DecompressString::class)]
final class StringCompressionTest extends TestCase
{
    /** @return array<string, array{0: 'bz2'|'gz', 1: int<1, 9>}> */
    public static function settingsProvider(): array
    {
        $cases = [];
        foreach (range(1, 9) as $level) {
            foreach (['gz', 'bz2'] as $type) {
                $cases[sprintf(
                    '%s at level %d',
                    $type,
                    $level,
                )] = [$type, $level];
            }
        }

        /** @psalm-var array<string, array{0: 'bz2'|'gz', 1: int<1, 9>}> */
        return $cases;
    }

    /**
     * @param 'bz2'|'gz' $adapter
     * @param int<1, 9> $level
     */
    #[DataProvider('settingsProvider')]
    public function testBasic(string $adapter, int $level): void
    {
        $compress = new CompressString([
            'adapter' => $adapter,
            'level'   => $level,
        ]);

        $decompress = new DecompressString([
            'adapter' => $adapter,
        ]);

        $input = 'Pepe the King Prawn';

        self::assertSame(
            $input,
            $decompress->__invoke(
                $compress->__invoke(
                    $input,
                ),
            ),
        );
    }

    public function testCustomAdapterCanBeUsed(): void
    {
        $adapter = new class implements StringCompressionAdapterInterface
        {
            public function compress(string $value): string
            {
                return 'Fozzie Bear';
            }

            public function decompress(string $value): string
            {
                return 'Miss Piggy';
            }
        };

        $compress   = new CompressString(['adapter' => $adapter]);
        $decompress = new DecompressString(['adapter' => $adapter]);

        self::assertSame('Fozzie Bear', $compress->filter('Kermit'));
        self::assertSame('Miss Piggy', $decompress->filter('Kermit'));
    }

    /** @return list<array{0: mixed}> */
    public static function unfilteredValues(): array
    {
        return [
            [(object) ['foo']],
            [['bar']],
            [123],
            [1.23],
            [false],
            [true],
            [null],
        ];
    }

    #[DataProvider('unfilteredValues')]
    public function testUnfilteredValues(mixed $input): void
    {
        $compress   = new CompressString();
        $decompress = new DecompressString();

        self::assertSame($input, $compress->filter($input));
        self::assertSame($input, $decompress->filter($input));
    }

    public function testMismatchedAdaptersCausesException(): void
    {
        $compress = new CompressString([
            'adapter' => 'gz',
        ]);

        $decompress = new DecompressString([
            'adapter' => 'bz2',
        ]);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Error during decompression');
        $decompress->filter(
            $compress->filter(
                'Something',
            ),
        );
    }
}
