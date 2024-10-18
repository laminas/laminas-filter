<?php

declare(strict_types=1);

namespace LaminasTest\Filter;

use Laminas\Filter\Exception\InvalidArgumentException;
use Laminas\Filter\PregReplace as PregReplaceFilter;
use LaminasTest\Filter\TestAsset\StringableObject;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use stdClass;

class PregReplaceTest extends TestCase
{
    public function testPassingPatternToConstructorSetsPattern(): void
    {
        $pattern = '#^controller/(?P<action>[a-z_-]+)#';
        $filter  = new PregReplaceFilter([
            'pattern'     => $pattern,
            'replacement' => 'foo/bar',
        ]);

        self::assertSame('foo/bar', $filter->filter('controller/whatever'));
    }

    public function testFilterPerformsRegexReplacement(): void
    {
        $filter = new PregReplaceFilter([
            'pattern'     => '#^controller/(?P<action>[a-z_-]+)#',
            'replacement' => 'foo/bar',
        ]);

        $string   = 'controller/action';
        $filtered = $filter($string);
        self::assertNotEquals($string, $filtered);
        self::assertSame('foo/bar', $filtered);
    }

    public function testFilterPerformsRegexReplacementWithArray(): void
    {
        $filter = new PregReplaceFilter([
            'pattern'     => '#^controller/(?P<action>[a-z_-]+)#',
            'replacement' => 'foo/bar',
        ]);

        $input = [
            'controller/action',
            'This should stay the same',
        ];

        $filtered = $filter($input);
        self::assertNotEquals($input, $filtered);
        self::assertSame([
            'foo/bar',
            'This should stay the same',
        ], $filtered);
    }

    /** @return list<array{0: mixed}> */
    public static function invalidPatternOptions(): array
    {
        return [
            [''],
            [null],
            [[]],
        ];
    }

    #[DataProvider('invalidPatternOptions')]
    public function testFilterThrowsExceptionWhenNoMatchPatternPresent(mixed $pattern): void
    {
        $this->expectException(InvalidArgumentException::class);
        /** @psalm-suppress MixedArgumentTypeCoercion */
        new PregReplaceFilter([
            'pattern' => $pattern,
        ]);
    }

    public function testPassingPatternWithExecModifierRaisesException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('"e" pattern modifier');

        new PregReplaceFilter([
            'pattern' => '/foo/e',
        ]);
    }

    public function testAllPatternsAreCheckedForTheEModifier(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('"e" pattern modifier');

        new PregReplaceFilter([
            'pattern' => [
                '/^foo',
                '/foo/e',
            ],
        ]);
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
        $filter = new PregReplaceFilter([
            'pattern'     => '#^controller/(?P<action>[a-z_-]+)#',
            'replacement' => 'foo/bar',
        ]);

        self::assertSame($input, $filter->filter($input));
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
    public function testShouldFilterNonStringScalarValues(float|bool|int $input): void
    {
        $filter = new PregReplaceFilter([
            'pattern'     => '#^controller/(?P<action>[a-z_-]+)#',
            'replacement' => 'foo/bar',
        ]);

        self::assertSame((string) $input, $filter($input));
    }

    public function testReplacementsAreProcessedForAllArrayMembers(): void
    {
        $filter = new PregReplaceFilter([
            'pattern'     => '/foo/',
            'replacement' => 'bar',
        ]);

        $input = [
            'a' => 'food',
            'b' => [
                'c' => 'moof',
                'd' => 'foobar',
            ],
            'c' => new StringableObject('oofoo'),
        ];

        $expect = [
            'a' => 'bard',
            'b' => [
                'c' => 'moof',
                'd' => 'barbar',
            ],
            'c' => 'oobar',
        ];

        self::assertSame($expect, $filter->filter($input));
    }

    public function testReplacementWithBackreferences(): void
    {
        $filter = new PregReplaceFilter([
            'pattern'     => '/(foo)([a-z]+)([0-9]+)/',
            'replacement' => '$3$1',
        ]);

        self::assertSame('1234567foo', $filter->filter('foobing1234567'));
    }
}
