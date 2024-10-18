<?php

declare(strict_types=1);

namespace LaminasTest\Filter;

use Laminas\Filter\StringTrim;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;

use function mb_chr;
use function str_repeat;

class StringTrimTest extends TestCase
{
    /** @return array<string, array{0: mixed, 1: mixed}> */
    public static function defaultBehaviourDataProvider(): array
    {
        return [
            'Ascii, no whitespace'    => ['string', 'string'],
            'Empty String'            => ['', ''],
            'Only Ascii whitespace'   => ["   \n\t   ", ''],
            'Only Unicode whitespace' => [str_repeat(mb_chr(0x2029), 10), ''],
            'Narrow Spaces'           => [mb_chr(0x202F) . 'Foo' . mb_chr(0x202F), 'Foo'],
            'Em Spaces'               => [mb_chr(0x2003) . 'Foo' . mb_chr(0x2003), 'Foo'],
            'Thin Spaces'             => [mb_chr(0x2009) . 'Foo' . mb_chr(0x2009), 'Foo'],
            'ZF-7183'                 => ['Зенд', 'Зенд'],
            'ZF-170'                  => ['Расчет', 'Расчет'],
            'ZF-7902'                 => ['/', '/'],
            'ZF-10891'                => ['   Зенд   ', 'Зенд'],
            // Non-String Input
            'Null'    => [null, null],
            'Integer' => [123, 123],
            'Float'   => [1.23, 1.23],
            'Array'   => [['Foo'], ['Foo']],
            'Boolean' => [true, true],
        ];
    }

    #[DataProvider('defaultBehaviourDataProvider')]
    public function testDefaultBehaviour(mixed $input, mixed $expect): void
    {
        $filter = new StringTrim();
        self::assertSame(
            $expect,
            $filter->filter($input),
        );
    }

    public function testAsciiCharListOption(): void
    {
        $filter = new StringTrim([
            'charlist' => '@&*',
        ]);

        self::assertSame('Foo', $filter->filter('**&&@@Foo@@&&**'));
        self::assertSame('Foo', $filter->filter('Foo'));
        self::assertSame('F&o&o', $filter->filter('F&o&o'));
    }

    public function testUnicodeCharListOption(): void
    {
        $filter = new StringTrim([
            'charlist' => '👍',
        ]);

        self::assertSame('Foo', $filter->filter('Foo👍👍'));
        self::assertSame('Foo', $filter->filter('👍Foo👍'));
        self::assertSame('Fo👍o', $filter->filter('Fo👍o👍'));
    }

    #[Group('Laminas-10891')]
    public function testLaminas10891(): void
    {
        $filter = new StringTrim([
            'charlist' => " \t\n\r\x0B・。",
        ]);

        self::assertSame('Зенд', $filter->filter('。  Зенд  。'));
    }

    /**
     * Ensures expected behavior with '0' as character list
     */
    #[Group('6261')]
    public function testEmptyCharList(): void
    {
        $filter = new StringTrim([
            'charlist' => '0',
        ]);

        self::assertSame('a0b', $filter->filter('00a0b00'));

        $filter = new StringTrim([
            'charlist' => '',
        ]);

        self::assertSame('str', $filter->filter(' str '));
    }

    public function testConfiguredCharListCanIncludeMetaChar(): void
    {
        $filter = new StringTrim(['charlist' => '!\\\s']);

        self::assertSame('Foo', $filter->filter('  ! Foo !  '));
    }
}
