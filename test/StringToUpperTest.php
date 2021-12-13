<?php

declare(strict_types=1);

namespace LaminasTest\Filter;

use Laminas\Filter\Exception;
use Laminas\Filter\StringToUpper as StringToUpperFilter;
use PHPUnit\Framework\TestCase;
use stdClass;

use function function_exists;
use function mb_internal_encoding;

class StringToUpperTest extends TestCase
{
    // @codingStandardsIgnoreStart
    /**
     * Laminas_Filter_StringToLower object
     *
     * @var StringToUpperFilter
     */
    protected $_filter;
    // @codingStandardsIgnoreEnd

    /**
     * Creates a new Laminas_Filter_StringToUpper object for each test method
     */
    public function setUp(): void
    {
        $this->_filter = new StringToUpperFilter();
    }

    /**
     * Ensures that the filter follows expected behavior
     */
    public function testBasic(): void
    {
        $filter         = $this->_filter;
        $valuesExpected = [
            'STRING' => 'STRING',
            'ABC1@3' => 'ABC1@3',
            'A b C'  => 'A B C',
        ];

        foreach ($valuesExpected as $input => $output) {
            $this->assertSame($output, $filter($input));
        }
    }

    /**
     * Ensures that the filter follows expected behavior with
     * specified encoding
     */
    public function testWithEncoding(): void
    {
        $filter         = $this->_filter;
        $valuesExpected = [
            'ü'     => 'Ü',
            'ñ'     => 'Ñ',
            'üñ123' => 'ÜÑ123',
        ];

        try {
            $filter->setEncoding('UTF-8');
            foreach ($valuesExpected as $input => $output) {
                $this->assertSame($output, $filter($input));
            }
        } catch (Exception\ExtensionNotLoadedException $e) {
            $this->assertContains('mbstring is required', $e->getMessage());
        }
    }

    public function testFalseEncoding(): void
    {
        if (! function_exists('mb_strtolower')) {
            $this->markTestSkipped('mbstring required');
        }

        $this->expectException(Exception\InvalidArgumentException::class);
        $this->expectExceptionMessage('is not supported');
        $this->_filter->setEncoding('aaaaa');
    }

    /**
     * @Laminas-8989
     */
    public function testInitiationWithEncoding(): void
    {
        $valuesExpected = [
            'ü'     => 'Ü',
            'ñ'     => 'Ñ',
            'üñ123' => 'ÜÑ123',
        ];

        try {
            $filter = new StringToUpperFilter(['encoding' => 'UTF-8']);
            foreach ($valuesExpected as $input => $output) {
                $this->assertSame($output, $filter($input));
            }
        } catch (Exception\ExtensionNotLoadedException $e) {
            $this->assertContains('mbstring is required', $e->getMessage());
        }
    }

    /**
     *  @Laminas-9058
     */
    public function testCaseInsensitiveEncoding(): void
    {
        $filter         = $this->_filter;
        $valuesExpected = [
            'ü'     => 'Ü',
            'ñ'     => 'Ñ',
            'üñ123' => 'ÜÑ123',
        ];

        try {
            $filter->setEncoding('UTF-8');
            foreach ($valuesExpected as $input => $output) {
                $this->assertSame($output, $filter($input));
            }

            $this->_filter->setEncoding('utf-8');
            foreach ($valuesExpected as $input => $output) {
                $this->assertSame($output, $filter($input));
            }

            $this->_filter->setEncoding('UtF-8');
            foreach ($valuesExpected as $input => $output) {
                $this->assertSame($output, $filter($input));
            }
        } catch (Exception\ExtensionNotLoadedException $e) {
            $this->assertContains('mbstring is required', $e->getMessage());
        }
    }

    /**
     * @group Laminas-9854
     */
    public function testDetectMbInternalEncoding(): void
    {
        if (! function_exists('mb_internal_encoding')) {
            $this->markTestSkipped("Function 'mb_internal_encoding' not available");
        }

        $this->assertSame(mb_internal_encoding(), $this->_filter->getEncoding());
    }

    public function returnUnfilteredDataProvider()
    {
        return [
            [null],
            [new stdClass()],
            [
                [
                    'lower case written',
                    'This should stay the same',
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
     * @group 7147
     */
    public function testFilterUsesGetEncodingMethod(): void
    {
        $filterMock = $this->getMockBuilder(StringToUpperFilter::class)
            ->setMethods(['getEncoding'])
            ->getMock();
        $filterMock->expects($this->once())
            ->method('getEncoding')
            ->with();
        $filterMock->filter('foo');
    }
}
