<?php

declare(strict_types=1);

namespace LaminasTest\Filter;

use ArrayObject;
use Laminas\Filter\Exception\DomainException;
use Laminas\Filter\HtmlEntities as HtmlEntitiesFilter;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;
use stdClass;

use function file_get_contents;
use function strlen;

use const ENT_COMPAT;
use const ENT_NOQUOTES;
use const ENT_QUOTES;

class HtmlEntitiesTest extends TestCase
{
    private HtmlEntitiesFilter $filter;

    /**
     * Creates a new Laminas\Filter\HtmlEntities object for each test method
     */
    public function setUp(): void
    {
        $this->filter = new HtmlEntitiesFilter();
    }

    /**
     * Ensures that the filter follows expected behavior
     */
    public function testBasic(): void
    {
        $valuesExpected = [
            'string' => 'string',
            '<'      => '&lt;',
            '>'      => '&gt;',
            '\''     => '&#039;',
            '"'      => '&quot;',
            '&'      => '&amp;',
        ];
        $filter         = $this->filter;
        foreach ($valuesExpected as $input => $output) {
            self::assertSame($output, $filter($input));
        }
    }

    /**
     * Ensures that getQuoteStyle() returns expected default value
     */
    public function testGetQuoteStyle(): void
    {
        self::assertSame(ENT_QUOTES, $this->filter->getQuoteStyle());
    }

    /**
     * Ensures that setQuoteStyle() follows expected behavior
     */
    public function testSetQuoteStyle(): void
    {
        $this->filter->setQuoteStyle(ENT_QUOTES);
        self::assertSame(ENT_QUOTES, $this->filter->getQuoteStyle());
    }

    /**
     * Ensures that getCharSet() returns expected default value
     */
    #[Group('Laminas-8715')]
    public function testGetCharSet(): void
    {
        self::assertSame('UTF-8', $this->filter->getCharSet());
    }

    /**
     * Ensures that setCharSet() follows expected behavior
     */
    public function testSetCharSet(): void
    {
        $this->filter->setCharSet('UTF-8');
        self::assertSame('UTF-8', $this->filter->getCharSet());
    }

    /**
     * Ensures that getDoubleQuote() returns expected default value
     */
    public function testGetDoubleQuote(): void
    {
        self::assertSame(true, $this->filter->getDoubleQuote());
    }

    /**
     * Ensures that setDoubleQuote() follows expected behavior
     */
    public function testSetDoubleQuote(): void
    {
        $this->filter->setDoubleQuote(false);
        self::assertSame(false, $this->filter->getDoubleQuote());
    }

    /**
     * Ensure that fluent interfaces are supported
     */
    #[Group('Laminas-3172')]
    public function testFluentInterface(): void
    {
        $instance = $this->filter->setCharSet('UTF-8')->setQuoteStyle(ENT_QUOTES)->setDoubleQuote(false);
        self::assertInstanceOf(HtmlEntitiesFilter::class, $instance);
    }

    /**
     * This test uses an ArrayObject in place of a Laminas\Config\Config instance;
     * they two are interchangeable in this scenario, as HtmlEntitiesFilter is
     * checking for arrays or Traversable instances.
     */
    #[Group('Laminas-8995')]
    public function testConfigObject(): void
    {
        $options = ['quotestyle' => 5, 'encoding' => 'ISO-8859-1'];
        $config  = new ArrayObject($options);

        $filter = new HtmlEntitiesFilter(
            $config
        );

        self::assertSame('ISO-8859-1', $filter->getEncoding());
        self::assertSame(5, $filter->getQuoteStyle());
    }

    /**
     * Ensures that when ENT_QUOTES is set, the filtered value has both 'single' and "double" quotes encoded
     */
    #[Group('Laminas-8962')]
    public function testQuoteStyleQuotesEncodeBoth(): void
    {
        $input  = "A 'single' and " . '"double"';
        $result = 'A &#039;single&#039; and &quot;double&quot;';

        $this->filter->setQuoteStyle(ENT_QUOTES);
        self::assertSame($result, $this->filter->filter($input));
    }

    /**
     * Ensures that when ENT_COMPAT is set, the filtered value has only "double" quotes encoded
     */
    #[Group('Laminas-8962')]
    public function testQuoteStyleQuotesEncodeDouble(): void
    {
        $input  = "A 'single' and " . '"double"';
        $result = "A 'single' and &quot;double&quot;";

        $this->filter->setQuoteStyle(ENT_COMPAT);
        self::assertSame($result, $this->filter->filter($input));
    }

    /**
     * Ensures that when ENT_NOQUOTES is set, the filtered value leaves both "double" and 'single' quotes un-altered
     */
    #[Group('Laminas-8962')]
    public function testQuoteStyleQuotesEncodeNone(): void
    {
        $input  = "A 'single' and " . '"double"';
        $result = "A 'single' and " . '"double"';

        $this->filter->setQuoteStyle(ENT_NOQUOTES);
        self::assertSame($result, $this->filter->filter($input));
    }

    #[Group('Laminas-11344')]
    public function testCorrectsForEncodingMismatch(): void
    {
        $string = file_get_contents(__DIR__ . '/_files/latin-1-text.txt');
        $result = $this->filter->filter($string);
        self::assertGreaterThan(0, strlen($result));
    }

    #[Group('Laminas-11344')]
    public function testStripsUnknownCharactersWhenEncodingMismatchDetected(): void
    {
        $string = file_get_contents(__DIR__ . '/_files/latin-1-text.txt');
        $result = $this->filter->filter($string);
        self::assertStringContainsString('&quot;&quot;', $result);
    }

    #[Group('Laminas-11344')]
    public function testRaisesExceptionIfEncodingMismatchDetectedAndFinalStringIsEmpty(): void
    {
        $string = file_get_contents(__DIR__ . '/_files/latin-1-dash-only.txt');
        $this->expectException(DomainException::class);
        $this->filter->filter($string);
    }

    /** @return list<array{0: mixed}> */
    public static function returnUnfilteredDataProvider(): array
    {
        return [
            [null],
            [new stdClass()],
            [
                [
                    '<',
                    '>',
                ],
            ],
        ];
    }

    #[DataProvider('returnUnfilteredDataProvider')]
    public function testReturnUnfiltered(mixed $input): void
    {
        self::assertSame($input, $this->filter->filter($input));
    }
}
