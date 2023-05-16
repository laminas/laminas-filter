<?php

declare(strict_types=1);

namespace LaminasTest\Filter;

use Laminas\Filter\Exception;
use Laminas\Filter\PregReplace as PregReplaceFilter;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use stdClass;

use function preg_match;

class PregReplaceTest extends TestCase
{
    private PregReplaceFilter $filter;

    public function setUp(): void
    {
        $this->filter = new PregReplaceFilter();
    }

    public function testDetectsPcreUnicodeSupport(): void
    {
        $enabled = (bool) @preg_match('/\pL/u', 'a');
        self::assertSame($enabled, PregReplaceFilter::hasPcreUnicodeSupport());
    }

    public function testPassingPatternToConstructorSetsPattern(): void
    {
        $pattern = '#^controller/(?P<action>[a-z_-]+)#';
        $filter  = new PregReplaceFilter($pattern);
        self::assertSame($pattern, $filter->getPattern());
    }

    public function testPassingReplacementToConstructorSetsReplacement(): void
    {
        $replace = 'foo/bar';
        $filter  = new PregReplaceFilter(null, $replace);
        self::assertSame($replace, $filter->getReplacement());
    }

    public function testPatternIsNullByDefault(): void
    {
        self::assertNull($this->filter->getPattern());
    }

    public function testPatternAccessorsWork(): void
    {
        $pattern = '#^controller/(?P<action>[a-z_-]+)#';
        $this->filter->setPattern($pattern);
        self::assertSame($pattern, $this->filter->getPattern());
    }

    public function testReplacementIsEmptyByDefault(): void
    {
        $replacement = $this->filter->getReplacement();
        self::assertEmpty($replacement);
    }

    public function testReplacementAccessorsWork(): void
    {
        $replacement = 'foo/bar';
        $this->filter->setReplacement($replacement);
        self::assertSame($replacement, $this->filter->getReplacement());
    }

    public function testFilterPerformsRegexReplacement(): void
    {
        $filter = $this->filter;
        $filter->setPattern('#^controller/(?P<action>[a-z_-]+)#')->setReplacement('foo/bar');

        $string   = 'controller/action';
        $filtered = $filter($string);
        self::assertNotEquals($string, $filtered);
        self::assertSame('foo/bar', $filtered);
    }

    public function testFilterPerformsRegexReplacementWithArray(): void
    {
        $filter = $this->filter;
        $filter->setPattern('#^controller/(?P<action>[a-z_-]+)#')->setReplacement('foo/bar');

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

    public function testFilterThrowsExceptionWhenNoMatchPatternPresent(): void
    {
        $filter = $this->filter;
        $string = 'controller/action';
        $filter->setReplacement('foo/bar');
        $this->expectException(Exception\RuntimeException::class);
        $this->expectExceptionMessage('does not have a valid pattern set');
        $filter($string);
    }

    public function testPassingPatternWithExecModifierRaisesException(): void
    {
        $filter = new PregReplaceFilter();
        $this->expectException(Exception\InvalidArgumentException::class);
        $this->expectExceptionMessage('"e" pattern modifier');
        $filter->setPattern('/foo/e');
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
        $filter = $this->filter;
        $filter->setPattern('#^controller/(?P<action>[a-z_-]+)#')->setReplacement('foo/bar');

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
        $filter = $this->filter;
        $filter->setPattern('#^controller/(?P<action>[a-z_-]+)#')->setReplacement('foo/bar');

        self::assertSame((string) $input, $filter($input));
    }
}
