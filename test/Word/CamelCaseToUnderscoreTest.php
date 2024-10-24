<?php

declare(strict_types=1);

namespace LaminasTest\Filter\Word;

use Laminas\Filter\Word\CamelCaseToUnderscore as CamelCaseToUnderscoreFilter;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class CamelCaseToUnderscoreTest extends TestCase
{
    /** @return list<array{string, string}> */
    public static function camelCasedWordsProvider(): array
    {
        return [
            ['CamelCasedWords', 'Camel_Cased_Words'],
            ['PaTitle', 'Pa_Title'],
            ['Pa2Title', 'Pa_2_Title'],
            ['Pa2aTitle', 'Pa_2_a_Title'],
        ];
    }

    #[DataProvider('camelCasedWordsProvider')]
    public function testFilterSeparatesCamelCasedWordsWithUnderscores(string $input, string $expected): void
    {
        $filter   = new CamelCaseToUnderscoreFilter();
        $filtered = $filter($input);

        self::assertSame($expected, $filtered);
    }
}
