<?php

declare(strict_types=1);

namespace LaminasTest\Filter;

use Laminas\Filter\StripTags as StripTagsFilter;
use PHPUnit\Framework\TestCase;
use stdClass;

use function iconv;

class StripTagsTest extends TestCase
{
    // @codingStandardsIgnoreStart
    /**
     * Laminas_Filter_StripTags object
     *
     * @var StripTagsFilter
     */
    protected $_filter;
    // @codingStandardsIgnoreEnd

    /**
     * Creates a new Laminas_Filter_StripTags object for each test method
     */
    public function setUp(): void
    {
        $this->_filter = new StripTagsFilter();
    }

    /**
     * Ensures that getTagsAllowed() returns expected default value
     */
    public function testGetTagsAllowed(): void
    {
        $this->assertSame([], $this->_filter->getTagsAllowed());
    }

    /**
     * Ensures that setTagsAllowed() follows expected behavior when provided a single tag
     */
    public function testSetTagsAllowedString(): void
    {
        $this->_filter->setTagsAllowed('b');
        $this->assertSame(['b' => []], $this->_filter->getTagsAllowed());
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
        $this->_filter->setTagsAllowed($tagsAllowed);
        $tagsAllowedExpected = [
            'b'   => [],
            'a'   => ['href' => null],
            'div' => ['id' => null, 'class' => null],
        ];
        $this->assertSame($tagsAllowedExpected, $this->_filter->getTagsAllowed());
    }

    /**
     * Ensures that getAttributesAllowed() returns expected default value
     */
    public function testGetAttributesAllowed(): void
    {
        $this->assertSame([], $this->_filter->getAttributesAllowed());
    }

    /**
     * Ensures that setAttributesAllowed() follows expected behavior when provided a single attribute
     */
    public function testSetAttributesAllowedString(): void
    {
        $this->_filter->setAttributesAllowed('class');
        $this->assertSame(['class' => null], $this->_filter->getAttributesAllowed());
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
        $this->_filter->setAttributesAllowed($attributesAllowed);
        $attributesAllowedExpected = [
            'class'  => null,
            'int'    => null,
            'string' => null,
        ];
        $this->assertSame($attributesAllowedExpected, $this->_filter->getAttributesAllowed());
    }

    /**
     * Ensures that a single unclosed tag is stripped in its entirety
     *
     * @return void
     */
    public function testFilterTagUnclosed1()
    {
        $filter   = $this->_filter;
        $input    = '<a href="http://example.com" Some Text';
        $expected = '';
        $this->assertSame($expected, $filter($input));
    }

    /**
     * Ensures that a single tag is stripped
     *
     * @return void
     */
    public function testFilterTag1()
    {
        $filter   = $this->_filter;
        $input    = '<a href="example.com">foo</a>';
        $expected = 'foo';
        $this->assertSame($expected, $filter($input));
    }

    /**
     * Ensures that singly nested tags are stripped
     *
     * @return void
     */
    public function testFilterTagNest1()
    {
        $filter   = $this->_filter;
        $input    = '<a href="example.com"><b>foo</b></a>';
        $expected = 'foo';
        $this->assertSame($expected, $filter($input));
    }

    /**
     * Ensures that two successive tags are stripped
     *
     * @return void
     */
    public function testFilterTag2()
    {
        $filter   = $this->_filter;
        $input    = '<a href="example.com">foo</a><b>bar</b>';
        $expected = 'foobar';
        $this->assertSame($expected, $filter($input));
    }

    /**
     * Ensures that an allowed tag is returned as lowercase and with backward-compatible XHTML ending, where supplied
     */
    public function testFilterTagAllowedBackwardCompatible(): void
    {
        $filter   = $this->_filter;
        $input    = '<BR><Br><bR><br/><br  /><br / ></br></bR>';
        $expected = '<br><br><br><br /><br /><br></br></br>';
        $this->_filter->setTagsAllowed('br');
        $this->assertSame($expected, $filter($input));
    }

    /**
     * Ensures that any greater-than symbols '>' are removed from text preceding a tag
     */
    public function testFilterTagPrefixGt(): void
    {
        $filter   = $this->_filter;
        $input    = '2 > 1 === true<br/>';
        $expected = '2  1 === true';
        $this->assertSame($expected, $filter($input));
    }

    /**
     * Ensures that any greater-than symbols '>' are removed from text having no tags
     */
    public function testFilterGt(): void
    {
        $filter   = $this->_filter;
        $input    = '2 > 1 === true ==> $object->property';
        $expected = '2  1 === true == $object-property';
        $this->assertSame($expected, $filter($input));
    }

    /**
     * Ensures that any greater-than symbols '>' are removed from text wrapping a tag
     */
    public function testFilterTagWrappedGt(): void
    {
        $filter   = $this->_filter;
        $input    = '2 > 1 === true <==> $object->property';
        $expected = '2  1 === true  $object-property';
        $this->assertSame($expected, $filter($input));
    }

    /**
     * Ensures that an attribute for an allowed tag is stripped
     */
    public function testFilterTagAllowedAttribute(): void
    {
        $filter      = $this->_filter;
        $tagsAllowed = 'img';
        $this->_filter->setTagsAllowed($tagsAllowed);
        $input    = '<IMG alt="foo" />';
        $expected = '<img />';
        $this->assertSame($expected, $filter($input));
    }

    /**
     * Ensures that an allowed tag with an allowed attribute is filtered as expected
     */
    public function testFilterTagAllowedAttributeAllowed(): void
    {
        $filter      = $this->_filter;
        $tagsAllowed = [
            'img' => 'alt',
        ];
        $this->_filter->setTagsAllowed($tagsAllowed);
        $input    = '<IMG ALT="FOO" />';
        $expected = '<img alt="FOO" />';
        $this->assertSame($expected, $filter($input));
    }

    /**
     * Ensures expected behavior when a greater-than symbol '>' appears in an allowed attribute's value
     *
     * Currently this is not unsupported; these symbols should be escaped when used in an attribute value.
     */
    public function testFilterTagAllowedAttributeAllowedGt(): void
    {
        $filter      = $this->_filter;
        $tagsAllowed = [
            'img' => 'alt',
        ];
        $this->_filter->setTagsAllowed($tagsAllowed);
        $input    = '<img alt="$object->property" />';
        $expected = '<img>property" /';
        $this->assertSame($expected, $filter($input));
    }

    /**
     * Ensures expected behavior when an escaped greater-than symbol '>' appears in an allowed attribute's value
     */
    public function testFilterTagAllowedAttributeAllowedGtEscaped(): void
    {
        $filter      = $this->_filter;
        $tagsAllowed = [
            'img' => 'alt',
        ];
        $this->_filter->setTagsAllowed($tagsAllowed);
        $input    = '<img alt="$object-&gt;property" />';
        $expected = '<img alt="$object-&gt;property" />';
        $this->assertSame($expected, $filter($input));
    }

    /**
     * Ensures that an unterminated attribute value does not affect other attributes but causes the corresponding
     * attribute to be removed in its entirety.
     */
    public function testFilterTagAllowedAttributeAllowedValueUnclosed(): void
    {
        $filter      = $this->_filter;
        $tagsAllowed = [
            'img' => ['alt', 'height', 'src', 'width'],
        ];
        $this->_filter->setTagsAllowed($tagsAllowed);
        $input    = '<img src="image.png" alt="square height="100" width="100" />';
        $expected = '<img src="image.png" alt="square height=" width="100" />';
        $this->assertSame($expected, $filter($input));
    }

    /**
     * Ensures that an allowed attribute having no value is removed (XHTML disallows attributes with no values)
     */
    public function testFilterTagAllowedAttributeAllowedValueMissing(): void
    {
        $filter      = $this->_filter;
        $tagsAllowed = [
            'input' => ['checked', 'name', 'type'],
        ];
        $this->_filter->setTagsAllowed($tagsAllowed);
        $input    = '<input name="foo" type="checkbox" checked />';
        $expected = '<input name="foo" type="checkbox" />';
        $this->assertSame($expected, $filter($input));
    }

    /**
     * Ensures that the filter works properly for the data reported on fw-general on 2007-05-26
     *
     * @see    http://www.nabble.com/question-about-tag-filter-p10813688s16154.html
     *
     * @return void
     */
    public function testFilter20070526()
    {
        $filter      = $this->_filter;
        $tagsAllowed = [
            'object' => ['width', 'height'],
            'param'  => ['name', 'value'],
            'embed'  => ['src', 'type', 'wmode', 'width', 'height'],
        ];
        $this->_filter->setTagsAllowed($tagsAllowed);
        $input    = '<object width="425" height="350"><param name="movie" value="http://www.example.com/path/to/movie">'
               . '</param><param name="wmode" value="transparent"></param><embed '
               . 'src="http://www.example.com/path/to/movie" type="application/x-shockwave-flash" '
               . 'wmode="transparent" width="425" height="350"></embed></object>';
        $expected = '<object width="425" height="350"><param name="movie" value="http://www.example.com/path/to/movie">'
               . '</param><param name="wmode" value="transparent"></param><embed '
               . 'src="http://www.example.com/path/to/movie" type="application/x-shockwave-flash" '
               . 'wmode="transparent" width="425" height="350"></embed></object>';
        $this->assertSame($expected, $filter($input));
    }

    /**
     * Ensures that a comment is stripped
     */
    public function testFilterComment(): void
    {
        $filter   = $this->_filter;
        $input    = '<!-- a comment -->';
        $expected = '';
        $this->assertSame($expected, $filter($input));
    }

    /**
     * Ensures that a comment wrapped with other strings is stripped
     */
    public function testFilterCommentWrapped(): void
    {
        $filter   = $this->_filter;
        $input    = 'foo<!-- a comment -->bar';
        $expected = 'foobar';
        $this->assertSame($expected, $filter($input));
    }

    /**
     * Ensures that a closing angle bracket in an allowed attribute does not break the parser
     *
     * @link   https://getlaminas.org/issues/browse/Laminas-3278
     */
    public function testClosingAngleBracketInAllowedAttributeValue(): void
    {
        $filter      = $this->_filter;
        $tagsAllowed = [
            'a' => 'href',
        ];
        $filter->setTagsAllowed($tagsAllowed);
        $input    = '<a href="Some &gt; Text">';
        $expected = '<a href="Some &gt; Text">';
        $this->assertSame($expected, $filter($input));
    }

    /**
     * Ensures that an allowed attribute's value may end with an equals sign '='
     *
     * @group Laminas-3293
     * @group Laminas-5983
     */
    public function testAllowedAttributeValueMayEndWithEquals(): void
    {
        $filter      = $this->_filter;
        $tagsAllowed = [
            'element' => 'attribute',
        ];
        $filter->setTagsAllowed($tagsAllowed);
        $input = '<element attribute="a=">contents</element>';
        $this->assertSame($input, $filter($input));
    }

    /**
     * @group Laminas-5983
     */
    public function testDisallowedAttributesSplitOverMultipleLinesShouldBeStripped(): void
    {
        $filter      = $this->_filter;
        $tagsAllowed = ['a' => 'href'];
        $filter->setTagsAllowed($tagsAllowed);
        $input    = '<a href="https://getlaminas.org/issues" onclick
=
    "alert(&quot;Gotcha&quot;); return false;">https://getlaminas.org/issues</a>';
        $filtered = $filter($input);
        $this->assertStringNotContainsString('onclick', $filtered);
    }

    /**
     * @Laminas-8828
     */
    public function testFilterIsoChars(): void
    {
        $filter   = $this->_filter;
        $input    = 'äöü<!-- a comment -->äöü';
        $expected = 'äöüäöü';
        $this->assertSame($expected, $filter($input));

        $input  = 'äöü<!-- a comment -->äöü';
        $input  = iconv("UTF-8", "ISO-8859-1", $input);
        $output = $filter($input);
        $this->assertNotEmpty($output);
    }

    /**
     * @Laminas-8828
     */
    public function testFilterIsoCharsInComment(): void
    {
        $filter   = $this->_filter;
        $input    = 'äöü<!--üßüßüß-->äöü';
        $expected = 'äöüäöü';
        $this->assertSame($expected, $filter($input));

        $input  = 'äöü<!-- a comment -->äöü';
        $input  = iconv("UTF-8", "ISO-8859-1", $input);
        $output = $filter($input);
        $this->assertNotEmpty($output);
    }

    /**
     * @Laminas-8828
     */
    public function testFilterSplitCommentTags(): void
    {
        $filter   = $this->_filter;
        $input    = 'äöü<!-->üßüßüß<-->äöü';
        $expected = 'äöüäöü';
        $this->assertSame($expected, $filter($input));
    }

    /**
     * @group Laminas-9434
     */
    public function testCommentWithTagInSameLine(): void
    {
        $filter   = $this->_filter;
        $input    = 'test <!-- testcomment --> test <div>div-content</div>';
        $expected = 'test  test div-content';
        $this->assertSame($expected, $filter($input));
    }

    /**
     * @group Laminas-9833
     */
    public function testMultiParamArray(): void
    {
        $filter = new StripTagsFilter(["a", "b", "hr"], [], true);

        $input    = 'test <a /> test <div>div-content</div>';
        $expected = 'test <a /> test div-content';
        $this->assertSame($expected, $filter->filter($input));
    }

    /**
     * @group Laminas-9828
     */
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
        $this->assertSame($expected, $filter->filter($input));
    }

    public function badCommentProvider()
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

    /**
     * @dataProvider badCommentProvider
     * @param string $input
     * @param string $expected
     */
    public function testBadCommentTags($input, $expected): void
    {
        $this->assertSame($expected, $this->_filter->filter($input));
    }

     /**
      * @group Laminas-10256
      */
    public function testNotClosedHtmlCommentAtEndOfString(): void
    {
        $input    = 'text<!-- not closed comment at the end';
        $expected = 'text';
        $this->assertSame($expected, $this->_filter->filter($input));
    }

    /**
     * @group Laminas-11617
     */
    public function testFilterCanAllowHyphenatedAttributeNames(): void
    {
        $input    = '<li data-disallowed="no!" data-name="Test User" data-id="11223"></li>';
        $expected = '<li data-name="Test User" data-id="11223"></li>';

        $this->_filter->setTagsAllowed('li');
        $this->_filter->setAttributesAllowed(['data-id', 'data-name']);

        $this->assertSame($expected, $this->_filter->filter($input));
    }

    public function returnUnfilteredDataProvider()
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

    /**
     * @dataProvider returnUnfilteredDataProvider
     */
    public function testReturnUnfiltered($input): void
    {
        $this->assertSame($input, $this->_filter->filter($input));
    }

    /**
     * @link https://github.com/zendframework/zf2/issues/5465
     */
    public function testAttributeValueofZeroIsNotRemoved(): void
    {
        $input    = '<div id="0" data-custom="0" class="bogus"></div>';
        $expected = '<div id="0" data-custom="0"></div>';
        $this->_filter->setTagsAllowed('div');
        $this->_filter->setAttributesAllowed(['id', 'data-custom']);
        $this->assertSame($expected, $this->_filter->filter($input));
    }
}
