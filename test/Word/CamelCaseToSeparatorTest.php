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

    /** @return list<array{string, string}> */
    public static function camelCasedWordsProvider(): array
    {
        return [
            ['SomeCamelCase', 'Some-Camel-Case'],
            ['Some12With5Numbers', 'Some-12-With-5-Numbers'],
            ['SomePDFInText', 'Some-PDF-In-Text'],
            ['123LeadingNumbers', '123-Leading-Numbers'],
            ['ItIs2016', 'It-Is-2016'],
            ['What-If', 'What-If'],
            ['ASingleLetterB', 'A-Single-Letter-B'],
            ['some_snake_case', 'some_snake_case'],
            ['Title_Snake_Case', 'Title-_-Snake-_-Case'],
            ['lower-with-dash', 'lowerwithdash'],
            ['FFS!', 'FFS-!'],
            ['WithA😃', 'With-A-😃'],
            ['PDF123', 'PDF-123'],
            ['EmojiInThe🤞Middle', 'Emoji-In-The-🤞-Middle'],
            ['12345', '12345'],
            ['123A', '123-A'],
            ['A123', 'A-123'],
            ['War&Peace', 'War-&-Peace'],
            ['lowerThenTitleCase', 'lower-Then-Title-Case'],
            ['123lower', '123-lower'],
            ['lower123', 'lower-123'],
            ['ItIsÜber', 'It-Is-Über'],
            ['SømeThing', 'Søme-Thing'],
        ];
    }

    #[DataProvider('camelCasedWordsProvider')]
    public function testFilterSeparatesCamelCasedWordsWithProvidedSeparator(string $input, string $expected): void
    {
        $filter   = new CamelCaseToSeparatorFilter(['separator' => '-']);
        $filtered = $filter($input);

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
