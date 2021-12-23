<?php

declare(strict_types=1);

namespace LaminasTest\Filter;

use ArrayObject;
use Exception;
use Laminas\Filter\Exception\DomainException;
use Laminas\Filter\HtmlEntities as HtmlEntitiesFilter;
use PHPUnit\Framework\TestCase;
use stdClass;

use function dirname;
use function file_get_contents;
use function phpversion;
use function restore_error_handler;
use function set_error_handler;
use function strlen;
use function version_compare;

use const E_NOTICE;
use const E_WARNING;
use const ENT_COMPAT;
use const ENT_NOQUOTES;
use const ENT_QUOTES;

class HtmlEntitiesTest extends TestCase
{
    // @codingStandardsIgnoreStart
    /**
     * Laminas\Filter\HtmlEntities object
     *
     * @var \Laminas\Filter\HtmlEntities
     */
    protected $_filter;
    // @codingStandardsIgnoreEnd

    /**
     * Creates a new Laminas\Filter\HtmlEntities object for each test method
     */
    public function setUp(): void
    {
        $this->_filter = new HtmlEntitiesFilter();
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
        $filter         = $this->_filter;
        foreach ($valuesExpected as $input => $output) {
            $this->assertSame($output, $filter($input));
        }
    }

    /**
     * Ensures that getQuoteStyle() returns expected default value
     */
    public function testGetQuoteStyle(): void
    {
        $this->assertSame(ENT_QUOTES, $this->_filter->getQuoteStyle());
    }

    /**
     * Ensures that setQuoteStyle() follows expected behavior
     */
    public function testSetQuoteStyle(): void
    {
        $this->_filter->setQuoteStyle(ENT_QUOTES);
        $this->assertSame(ENT_QUOTES, $this->_filter->getQuoteStyle());
    }

    /**
     * Ensures that getCharSet() returns expected default value
     *
     * @group Laminas-8715
     */
    public function testGetCharSet(): void
    {
        $this->assertSame('UTF-8', $this->_filter->getCharSet());
    }

    /**
     * Ensures that setCharSet() follows expected behavior
     */
    public function testSetCharSet(): void
    {
        $this->_filter->setCharSet('UTF-8');
        $this->assertSame('UTF-8', $this->_filter->getCharSet());
    }

    /**
     * Ensures that getDoubleQuote() returns expected default value
     */
    public function testGetDoubleQuote(): void
    {
        $this->assertSame(true, $this->_filter->getDoubleQuote());
    }

    /**
     * Ensures that setDoubleQuote() follows expected behavior
     */
    public function testSetDoubleQuote(): void
    {
        $this->_filter->setDoubleQuote(false);
        $this->assertSame(false, $this->_filter->getDoubleQuote());
    }

    /**
     * Ensure that fluent interfaces are supported
     *
     * @group Laminas-3172
     */
    public function testFluentInterface(): void
    {
        $instance = $this->_filter->setCharSet('UTF-8')->setQuoteStyle(ENT_QUOTES)->setDoubleQuote(false);
        $this->assertInstanceOf(HtmlEntitiesFilter::class, $instance);
    }

    /**
     * This test uses an ArrayObject in place of a Laminas\Config\Config instance;
     * they two are interchangeable in this scenario, as HtmlEntitiesFilter is
     * checking for arrays or Traversable instances.
     *
     * @group Laminas-8995
     */
    public function testConfigObject(): void
    {
        $options = ['quotestyle' => 5, 'encoding' => 'ISO-8859-1'];
        $config  = new ArrayObject($options);

        $filter = new HtmlEntitiesFilter(
            $config
        );

        $this->assertSame('ISO-8859-1', $filter->getEncoding());
        $this->assertSame(5, $filter->getQuoteStyle());
    }

    /**
     * Ensures that when ENT_QUOTES is set, the filtered value has both 'single' and "double" quotes encoded
     *
     * @group  Laminas-8962
     */
    public function testQuoteStyleQuotesEncodeBoth(): void
    {
        $input  = "A 'single' and " . '"double"';
        $result = 'A &#039;single&#039; and &quot;double&quot;';

        $this->_filter->setQuoteStyle(ENT_QUOTES);
        $this->assertSame($result, $this->_filter->filter($input));
    }

    /**
     * Ensures that when ENT_COMPAT is set, the filtered value has only "double" quotes encoded
     *
     * @group  Laminas-8962
     */
    public function testQuoteStyleQuotesEncodeDouble(): void
    {
        $input  = "A 'single' and " . '"double"';
        $result = "A 'single' and &quot;double&quot;";

        $this->_filter->setQuoteStyle(ENT_COMPAT);
        $this->assertSame($result, $this->_filter->filter($input));
    }

    /**
     * Ensures that when ENT_NOQUOTES is set, the filtered value leaves both "double" and 'single' quotes un-altered
     *
     * @group  Laminas-8962
     */
    public function testQuoteStyleQuotesEncodeNone(): void
    {
        $input  = "A 'single' and " . '"double"';
        $result = "A 'single' and " . '"double"';

        $this->_filter->setQuoteStyle(ENT_NOQUOTES);
        $this->assertSame($result, $this->_filter->filter($input));
    }

    /**
     * @group Laminas-11344
     */
    public function testCorrectsForEncodingMismatch(): void
    {
        if (version_compare(phpversion(), '5.4', '>=')) {
            $this->markTestIncomplete('Code to test is not compatible with PHP 5.4 ');
        }

        $string = file_get_contents(dirname(__FILE__) . '/_files/latin-1-text.txt');

        // restore_error_handler can emit an E_WARNING; let's ignore that, as
        // we want to test the returned value
        set_error_handler([$this, 'errorHandler'], E_NOTICE | E_WARNING);
        $result = $this->_filter->filter($string);
        restore_error_handler();

        $this->assertGreaterThan(0, strlen($result));
    }

    /**
     * @group Laminas-11344
     */
    public function testStripsUnknownCharactersWhenEncodingMismatchDetected(): void
    {
        if (version_compare(phpversion(), '5.4', '>=')) {
            $this->markTestIncomplete('Code to test is not compatible with PHP 5.4 ');
        }

        $string = file_get_contents(dirname(__FILE__) . '/_files/latin-1-text.txt');

        // restore_error_handler can emit an E_WARNING; let's ignore that, as
        // we want to test the returned value
        set_error_handler([$this, 'errorHandler'], E_NOTICE | E_WARNING);
        $result = $this->_filter->filter($string);
        restore_error_handler();

        $this->assertContains('&quot;&quot;', $result);
    }

    /**
     * @group Laminas-11344
     */
    public function testRaisesExceptionIfEncodingMismatchDetectedAndFinalStringIsEmpty(): void
    {
        if (version_compare(phpversion(), '5.4', '>=')) {
            $this->markTestIncomplete('Code to test is not compatible with PHP 5.4 ');
        }

        $string = file_get_contents(dirname(__FILE__) . '/_files/latin-1-dash-only.txt');

        // restore_error_handler can emit an E_WARNING; let's ignore that, as
        // we want to test the returned value
        // Also, explicit try, so that we don't mess up PHPUnit error handlers
        set_error_handler([$this, 'errorHandler'], E_NOTICE | E_WARNING);
        try {
            $result = $this->_filter->filter($string);
            $this->fail('Expected exception from single non-utf-8 character');
        } catch (Exception $e) {
            $this->assertInstanceOf(DomainException::class, $e);
        }
    }

    public function returnUnfilteredDataProvider()
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

    /**
     * @dataProvider returnUnfilteredDataProvider
     */
    public function testReturnUnfiltered($input): void
    {
        $this->assertSame($input, $this->_filter->filter($input));
    }

    /**
     * Null error handler; used when wanting to ignore specific error types
     */
    public function errorHandler($errno, $errstr)
    {
    }
}
