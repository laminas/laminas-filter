<?php

declare(strict_types=1);

namespace LaminasTest\Filter\Word;

use Laminas\Filter\Word\CamelCaseToSeparator as CamelCaseToSeparatorFilter;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use stdClass;

class CamelCaseToSeparatorTest extends TestCase
{
    public function testFilterSeparatesCamelCasedWordsWithSpacesByDefault(): void
    {
        $string   = 'CamelCasedWords';
        $filter   = new CamelCaseToSeparatorFilter();
        $filtered = $filter($string);

        self::assertNotEquals($string, $filtered);
        self::assertSame('Camel Cased Words', $filtered);
    }

    public static function camelCasedWordsProvider(): array
    {
        return [
            ['CamelCasedWords', 'Camel-Cased-Words'],
            ['123LeadingNumber', '123-Leading-Number'],
            ['Number12InTheMiddle', 'Number-12-In-The-Middle'],
            ['ANumberAtTheEnd42', 'A-Number-At-The-End-42'],
            ['SomePDFFile', 'Some-PDF-File'],
            ['Has-ExistingSeparator', 'Has-Existing-Separator'],
            ['What_Happens_Here', 'What_Happens_Here'],
            ['leadingLowerCase', 'leading-Lower-Case'],
            ['¿MïsÜsingUnicödeSŷmbols?', '¿Mïs-Üsing-Unicöde-Sŷmbols?'],
        ];
    }

    #[DataProvider('camelCasedWordsProvider')]
    public function testFilterSeparatesCamelCasedWordsWithProvidedSeparator(string $input, string $expected): void
    {
        $filter   = new CamelCaseToSeparatorFilter(['separator' => '-']);
        $filtered = $filter($input);

        self::assertNotEquals($input, $filtered);
        self::assertSame($expected, $filtered);
    }

    public function testFilterSeperatesMultipleUppercasedLettersAndUnderscores(): void
    {
        $string   = 'TheseAre_SOME_CamelCASEDWords';
        $filter   = new CamelCaseToSeparatorFilter(['separator' => '_']);
        $filtered = $filter($string);

        self::assertNotEquals($string, $filtered);
        self::assertSame('These_Are_SOME_Camel_CASED_Words', $filtered);
    }

    public function testFilterSupportArray(): void
    {
        $filter = new CamelCaseToSeparatorFilter();

        $input = [
            'CamelCasedWords',
            'somethingDifferent',
        ];

        $filtered = $filter($input);

        self::assertNotEquals($input, $filtered);
        self::assertSame(['Camel Cased Words', 'something Different'], $filtered);
    }

    /** @return list<array{0: mixed}> */
    public static function returnUnfilteredDataProvider(): array
    {
        return [
            [null],
            [new stdClass()],
        ];
    }

    #[DataProvider('returnUnfilteredDataProvider')]
    public function testReturnUnfiltered(mixed $input): void
    {
        $filter = new CamelCaseToSeparatorFilter();

        self::assertSame($input, $filter($input));
    }

    /**
     * @return array<int|float|bool>[]
     */
    public static function returnNonStringScalarValues(): array
    {
        return [
            [1],
            [1.0],
            [true],
            [false],
        ];
    }

    #[DataProvider('returnNonStringScalarValues')]
    public function testShouldFilterNonStringScalarValues(int|float|bool $input): void
    {
        $filter = new CamelCaseToSeparatorFilter();

        self::assertSame((string) $input, $filter($input));
    }
}
