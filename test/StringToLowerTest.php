<?php

declare(strict_types=1);

namespace LaminasTest\Filter;

use Laminas\Filter\Exception;
use Laminas\Filter\StringToLower as StringToLowerFilter;
use PHPUnit\Framework\TestCase;
use stdClass;

use function mb_internal_encoding;

class StringToLowerTest extends TestCase
{
    // @codingStandardsIgnoreStart
    /**
     * Laminas_Filter_StringToLower object
     *
     * @var StringToLowerFilter
     */
    protected $_filter;
    // @codingStandardsIgnoreEnd

    /**
     * Creates a new Laminas_Filter_StringToLower object for each test method
     */
    public function setUp(): void
    {
        $this->_filter = new StringToLowerFilter();
    }

    /**
     * Ensures that the filter follows expected behavior
     */
    public function testBasic(): void
    {
        $filter         = $this->_filter;
        $valuesExpected = [
            'string' => 'string',
            'aBc1@3' => 'abc1@3',
            'A b C'  => 'a b c',
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
            'Ü'     => 'ü',
            'Ñ'     => 'ñ',
            'ÜÑ123' => 'üñ123',
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
            'Ü'     => 'ü',
            'Ñ'     => 'ñ',
            'ÜÑ123' => 'üñ123',
        ];

        try {
            $filter = new StringToLowerFilter(['encoding' => 'UTF-8']);
            foreach ($valuesExpected as $input => $output) {
                $this->assertSame($output, $filter($input));
            }
        } catch (Exception\ExtensionNotLoadedException $e) {
            $this->assertContains('mbstring is required', $e->getMessage());
        }
    }

    /**
     * @Laminas-9058
     */
    public function testCaseInsensitiveEncoding(): void
    {
        $filter         = $this->_filter;
        $valuesExpected = [
            'Ü'     => 'ü',
            'Ñ'     => 'ñ',
            'ÜÑ123' => 'üñ123',
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
        $this->assertSame(mb_internal_encoding(), $this->_filter->getEncoding());
    }

    public function returnUnfilteredDataProvider()
    {
        return [
            [null],
            [new stdClass()],
            [
                [
                    'UPPER CASE WRITTEN',
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
        $filterMock = $this->getMockBuilder(StringToLowerFilter::class)
            ->setMethods(['getEncoding'])
            ->getMock();
        $filterMock->expects($this->once())
            ->method('getEncoding')
            ->with();
        $filterMock->filter('foo');
    }
}
