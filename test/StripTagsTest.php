<?php

declare(strict_types=1);

namespace LaminasTest\Filter;

use Laminas\Filter\StripTags as StripTagsFilter;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;
use stdClass;

use function iconv;

class StripTagsTest extends TestCase
{
    private StripTagsFilter $filter;

    public function setUp(): void
    {
        $this->filter = new StripTagsFilter();
    }

    /**
     * Ensures that getTagsAllowed() returns expected default value
     */
    public function testGetTagsAllowed(): void
    {
        self::assertSame([], $this->filter->getTagsAllowed());
    }

    /**
     * Ensures that setTagsAllowed() follows expected behavior when provided a single tag
     */
    public function testSetTagsAllowedString(): void
    {
        $this->filter->setTagsAllowed('b');
        self::assertSame(['b' => []], $this->filter->getTagsAllowed());
    }

    /**
     * Ensures that setTagsAllowed() follows expected behavior when provided an array of tags
     */
    public function testSetTagsAllowedArray(): void
    {
        $tagsAllowed = [
            'b',
            'a'   => 'href',
            'div' => ['id', 'class'],
        ];
        $this->filter->setTagsAllowed($tagsAllowed);
        $tagsAllowedExpected = [
            'b'   => [],
            'a'   => ['href' => null],
            'div' => ['id' => null, 'class' => null],
        ];
        self::assertSame($tagsAllowedExpected, $this->filter->getTagsAllowed());
    }

    /**
     * Ensures that getAttributesAllowed() returns expected default value
     */
    public function testGetAttributesAllowed(): void
    {
        self::assertSame([], $this->filter->getAttributesAllowed());
    }

    /**
     * Ensures that setAttributesAllowed() follows expected behavior when provided a single attribute
     */
    public function testSetAttributesAllowedString(): void
    {
        $this->filter->setAttributesAllowed('class');
        self::assertSame(['class' => null], $this->filter->getAttributesAllowed());
    }

    /**
     * Ensures that setAttributesAllowed() follows expected behavior when provided an array of attributes
     */
    public function testSetAttributesAllowedArray(): void
    {
        $attributesAllowed = [
            'clAss',
            4    => 'inT',
            'ok' => 'String',
            null,
        ];
        /** @psalm-suppress ArgumentTypeCoercion */
        $this->filter->setAttributesAllowed($attributesAllowed);
        $attributesAllowedExpected = [
            'class'  => null,
            'int'    => null,
            'string' => null,
        ];
        self::assertSame($attributesAllowedExpected, $this->filter->getAttributesAllowed());
    }

    /**
     * Ensures that a single unclosed tag is stripped in its entirety
     */
    public function testFilterTagUnclosed1(): void
    {
        $filter   = $this->filter;
        $input    = '<a href="http://example.com" Some Text';
        $expected = '';
        self::assertSame($expected, $filter($input));
    }

    /**
     * Ensures that a single tag is stripped
     */
    public function testFilterTag1(): void
    {
        $filter   = $this->filter;
        $input    = '<a href="example.com">foo</a>';
        $expected = 'foo';
        self::assertSame($expected, $filter($input));
    }

    /**
     * Ensures that singly nested tags are stripped
     */
    public function testFilterTagNest1(): void
    {
        $filter   = $this->filter;
        $input    = '<a href="example.com"><b>foo</b></a>';
        $expected = 'foo';
        self::assertSame($expected, $filter($input));
    }

    /**
     * Ensures that two successive tags are stripped
     */
    public function testFilterTag2(): void
    {
        $filter   = $this->filter;
        $input    = '<a href="example.com">foo</a><b>bar</b>';
        $expected = 'foobar';
        self::assertSame($expected, $filter($input));
    }

    /**
     * Ensures that an allowed tag is returned as lowercase and with backward-compatible XHTML ending, where supplied
     */
    public function testFilterTagAllowedBackwardCompatible(): void
    {
        $filter   = $this->filter;
        $input    = '<BR><Br><bR><br/><br  /><br / ></br></bR>';
        $expected = '<br><br><br><br /><br /><br></br></br>';
        $this->filter->setTagsAllowed('br');
        self::assertSame($expected, $filter($input));
    }

    /**
     * Ensures that any greater-than symbols '>' are removed from text preceding a tag
     */
    public function testFilterTagPrefixGt(): void
    {
        $filter   = $this->filter;
        $input    = '2 > 1 === true<br/>';
        $expected = '2  1 === true';
        self::assertSame($expected, $filter($input));
    }

    /**
     * Ensures that any greater-than symbols '>' are removed from text having no tags
     */
    public function testFilterGt(): void
    {
        $filter   = $this->filter;
        $input    = '2 > 1 === true ==> $object->property';
        $expected = '2  1 === true == $object-property';
        self::assertSame($expected, $filter($input));
    }

    /**
     * Ensures that any greater-than symbols '>' are removed from text wrapping a tag
     */
    public function testFilterTagWrappedGt(): void
    {
        $filter   = $this->filter;
        $input    = '2 > 1 === true <==> $object->property';
        $expected = '2  1 === true  $object-property';
        self::assertSame($expected, $filter($input));
    }

    /**
     * Ensures that an attribute for an allowed tag is stripped
     */
    public function testFilterTagAllowedAttribute(): void
    {
        $filter      = $this->filter;
        $tagsAllowed = 'img';
        $this->filter->setTagsAllowed($tagsAllowed);
        $input    = '<IMG alt="foo" />';
        $expected = '<img />';
        self::assertSame($expected, $filter($input));
    }

    /**
     * Ensures that an allowed tag with an allowed attribute is filtered as expected
     */
    public function testFilterTagAllowedAttributeAllowed(): void
    {
        $filter      = $this->filter;
        $tagsAllowed = [
            'img' => 'alt',
        ];
        $this->filter->setTagsAllowed($tagsAllowed);
        $input    = '<IMG ALT="FOO" />';
        $expected = '<img alt="FOO" />';
        self::assertSame($expected, $filter($input));
    }

    /**
     * Ensures expected behavior when a greater-than symbol '>' appears in an allowed attribute's value
     *
     * Currently this is not unsupported; these symbols should be escaped when used in an attribute value.
     */
    public function testFilterTagAllowedAttributeAllowedGt(): void
    {
        $filter      = $this->filter;
        $tagsAllowed = [
            'img' => 'alt',
        ];
        $this->filter->setTagsAllowed($tagsAllowed);
        $input    = '<img alt="$object->property" />';
        $expected = '<img>property" /';
        self::assertSame($expected, $filter($input));
    }

    /**
     * Ensures expected behavior when an escaped greater-than symbol '>' appears in an allowed attribute's value
     */
    public function testFilterTagAllowedAttributeAllowedGtEscaped(): void
    {
        $filter      = $this->filter;
        $tagsAllowed = [
            'img' => 'alt',
        ];
        $this->filter->setTagsAllowed($tagsAllowed);
        $input    = '<img alt="$object-&gt;property" />';
        $expected = '<img alt="$object-&gt;property" />';
        self::assertSame($expected, $filter($input));
    }

    /**
     * Ensures that an unterminated attribute value does not affect other attributes but causes the corresponding
     * attribute to be removed in its entirety.
     */
    public function testFilterTagAllowedAttributeAllowedValueUnclosed(): void
    {
        $filter      = $this->filter;
        $tagsAllowed = [
            'img' => ['alt', 'height', 'src', 'width'],
        ];
        $this->filter->setTagsAllowed($tagsAllowed);
        $input    = '<img src="image.png" alt="square height="100" width="100" />';
        $expected = '<img src="image.png" alt="square height=" width="100" />';
        self::assertSame($expected, $filter($input));
    }

    /**
     * Ensures that an allowed attribute having no value is removed (XHTML disallows attributes with no values)
     */
    public function testFilterTagAllowedAttributeAllowedValueMissing(): void
    {
        $filter      = $this->filter;
        $tagsAllowed = [
            'input' => ['checked', 'name', 'type'],
        ];
        $this->filter->setTagsAllowed($tagsAllowed);
        $input    = '<input name="foo" type="checkbox" checked />';
        $expected = '<input name="foo" type="checkbox" />';
        self::assertSame($expected, $filter($input));
    }

    /**
     * Ensures that the filter works properly for the data reported on fw-general on 2007-05-26
     *
     * @see    http://www.nabble.com/question-about-tag-filter-p10813688s16154.html
     */
    public function testFilter20070526(): void
    {
        $filter      = $this->filter;
        $tagsAllowed = [
            'object' => ['width', 'height'],
            'param'  => ['name', 'value'],
            'embed'  => ['src', 'type', 'wmode', 'width', 'height'],
        ];
        $this->filter->setTagsAllowed($tagsAllowed);
        $input    = '<object width="425" height="350"><param name="movie" value="http://www.example.com/path/to/movie">'
               . '</param><param name="wmode" value="transparent"></param><embed '
               . 'src="http://www.example.com/path/to/movie" type="application/x-shockwave-flash" '
               . 'wmode="transparent" width="425" height="350"></embed></object>';
        $expected = '<object width="425" height="350"><param name="movie" value="http://www.example.com/path/to/movie">'
               . '</param><param name="wmode" value="transparent"></param><embed '
               . 'src="http://www.example.com/path/to/movie" type="application/x-shockwave-flash" '
               . 'wmode="transparent" width="425" height="350"></embed></object>';
        self::assertSame($expected, $filter($input));
    }

    /**
     * Ensures that a comment is stripped
     */
    public function testFilterComment(): void
    {
        $filter   = $this->filter;
        $input    = '<!-- a comment -->';
        $expected = '';
        self::assertSame($expected, $filter($input));
    }

    /**
     * Ensures that a comment wrapped with other strings is stripped
     */
    public function testFilterCommentWrapped(): void
    {
        $filter   = $this->filter;
        $input    = 'foo<!-- a comment -->bar';
        $expected = 'foobar';
        self::assertSame($expected, $filter($input));
    }

    /**
     * Ensures that a closing angle bracket in an allowed attribute does not break the parser
     *
     * @link   https://getlaminas.org/issues/browse/Laminas-3278
     */
    public function testClosingAngleBracketInAllowedAttributeValue(): void
    {
        $filter      = $this->filter;
        $tagsAllowed = [
            'a' => 'href',
        ];
        $filter->setTagsAllowed($tagsAllowed);
        $input    = '<a href="Some &gt; Text">';
        $expected = '<a href="Some &gt; Text">';
        self::assertSame($expected, $filter($input));
    }

    /**
     * Ensures that an allowed attribute's value may end with an equals sign '='
     */
    #[Group('Laminas-3293')]
    #[Group('Laminas-5983')]
    public function testAllowedAttributeValueMayEndWithEquals(): void
    {
        $filter      = $this->filter;
        $tagsAllowed = [
            'element' => 'attribute',
        ];
        $filter->setTagsAllowed($tagsAllowed);
        $input = '<element attribute="a=">contents</element>';
        self::assertSame($input, $filter($input));
    }

    #[Group('Laminas-5983')]
    public function testDisallowedAttributesSplitOverMultipleLinesShouldBeStripped(): void
    {
        $filter      = $this->filter;
        $tagsAllowed = ['a' => 'href'];
        $filter->setTagsAllowed($tagsAllowed);
        $input    = '<a href="https://getlaminas.org/issues" onclick
=
    "alert(&quot;Gotcha&quot;); return false;">https://getlaminas.org/issues</a>';
        $filtered = $filter($input);
        self::assertStringNotContainsString('onclick', $filtered);
    }

    /**
     * @Laminas-8828
     */
    public function testFilterIsoChars(): void
    {
        $filter   = $this->filter;
        $input    = 'äöü<!-- a comment -->äöü';
        $expected = 'äöüäöü';
        self::assertSame($expected, $filter($input));

        $input  = 'äöü<!-- a comment -->äöü';
        $input  = iconv("UTF-8", "ISO-8859-1", $input);
        $output = $filter($input);
        self::assertNotEmpty($output);
    }

    /**
     * @Laminas-8828
     */
    public function testFilterIsoCharsInComment(): void
    {
        $filter   = $this->filter;
        $input    = 'äöü<!--üßüßüß-->äöü';
        $expected = 'äöüäöü';
        self::assertSame($expected, $filter($input));

        $input  = 'äöü<!-- a comment -->äöü';
        $input  = iconv("UTF-8", "ISO-8859-1", $input);
        $output = $filter($input);
        self::assertNotEmpty($output);
    }

    /**
     * @Laminas-8828
     */
    public function testFilterSplitCommentTags(): void
    {
        $filter   = $this->filter;
        $input    = 'äöü<!-->üßüßüß<-->äöü';
        $expected = 'äöüäöü';
        self::assertSame($expected, $filter($input));
    }

    #[Group('Laminas-9434')]
    public function testCommentWithTagInSameLine(): void
    {
        $filter   = $this->filter;
        $input    = 'test <!-- testcomment --> test <div>div-content</div>';
        $expected = 'test  test div-content';
        self::assertSame($expected, $filter($input));
    }

    #[Group('Laminas-9833')]
    public function testMultiParamArray(): void
    {
        $filter = new StripTagsFilter(["a", "b", "hr"], [], true);

        $input    = 'test <a /> test <div>div-content</div>';
        $expected = 'test <a /> test div-content';
        self::assertSame($expected, $filter->filter($input));
    }

    #[Group('Laminas-9828')]
    public function testMultiQuoteInput(): void
    {
        $filter = new StripTagsFilter(
            [
                'allowTags'    => 'img',
                'allowAttribs' => ['width', 'height', 'src'],
            ]
        );

        $input    = '<img width="10" height="10" src=\'wont_be_matched.jpg\'>';
        $expected = '<img width="10" height="10" src=\'wont_be_matched.jpg\'>';
        self::assertSame($expected, $filter->filter($input));
    }

    /** @return list<array{0: string, 1: string}> */
    public static function badCommentProvider(): array
    {
        return [
            ['A <!--> B', 'A '], // Should be treated as just an open
            ['A <!---> B', 'A '], // Should be treated as just an open
            ['A <!----> B', 'A  B'],
            ['A <!-- --> B', 'A  B'],
            ['A <!--> B <!--> C', 'A  C'],
            ['A <!-- -- > -- > --> B', 'A  B'],
            ["A <!-- B\n C\n D --> E", 'A  E'],
            ["A <!-- B\n <!-- C\n D --> E", 'A  E'],
            ['A <!-- B <!-- C --> D --> E', 'A  D -- E'],
            ["A <!--\n B\n <!-- C\n D \n\n\n--> E", 'A  E'],
            ['A <!--My favorite operators are > and <!--> B', 'A  B'],
        ];
    }

    #[DataProvider('badCommentProvider')]
    public function testBadCommentTags(string $input, string $expected): void
    {
        self::assertSame($expected, $this->filter->filter($input));
    }

     #[Group('Laminas-10256')]
    public function testNotClosedHtmlCommentAtEndOfString(): void
    {
        $input    = 'text<!-- not closed comment at the end';
        $expected = 'text';
        self::assertSame($expected, $this->filter->filter($input));
    }

    #[Group('Laminas-11617')]
    public function testFilterCanAllowHyphenatedAttributeNames(): void
    {
        $input    = '<li data-disallowed="no!" data-name="Test User" data-id="11223"></li>';
        $expected = '<li data-name="Test User" data-id="11223"></li>';

        $this->filter->setTagsAllowed('li');
        $this->filter->setAttributesAllowed(['data-id', 'data-name']);

        self::assertSame($expected, $this->filter->filter($input));
    }

    /** @return list<array{0: mixed}> */
    public static function returnUnfilteredDataProvider(): array
    {
        return [
            [null],
            [new stdClass()],
            [
                [
                    '<li data-name="Test User" data-id="11223"></li>',
                    '<li data-name="Test User 2" data-id="456789"></li>',
                ],
            ],
        ];
    }

    #[DataProvider('returnUnfilteredDataProvider')]
    public function testReturnUnfiltered(mixed $input): void
    {
        self::assertSame($input, $this->filter->filter($input));
    }

    /**
     * @link https://github.com/zendframework/zf2/issues/5465
     */
    public function testAttributeValueOfZeroIsNotRemoved(): void
    {
        $input    = '<div id="0" data-custom="0" class="bogus"></div>';
        $expected = '<div id="0" data-custom="0"></div>';
        $this->filter->setTagsAllowed('div');
        $this->filter->setAttributesAllowed(['id', 'data-custom']);
        self::assertSame($expected, $this->filter->filter($input));
    }
}
